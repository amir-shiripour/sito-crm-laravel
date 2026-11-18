@extends('layouts.user')

@php
    $title = 'مدیریت درخواست‌های ثبت نام';
@endphp

@section('content')
<div class="space-y-6 font-sans">

    {{-- هدر صفحه و کارت‌های آماری --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 p-4 sm:p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl sm:text-2xl font-bold shrink-0">
                    📋
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-base sm:text-xl font-black text-slate-900 dark:text-white">درخواست‌های ثبت‌نام و عضویت</h1>
                        <span class="px-2.5 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 text-[11px] font-bold border border-indigo-100 dark:border-indigo-500/20 whitespace-nowrap">
                            تایید دستی مدارک
                        </span>
                    </div>
                </div>
            </div>

            {{-- لینک سریع --}}
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                @if(\Illuminate\Support\Facades\Route::has('user.fieldservice.technicians.index'))
                <a href="{{ route('user.fieldservice.technicians.index') }}"
                   class="w-full sm:w-auto justify-center px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-2">
                    <span>🔧</span>
                    <span>ناوگان متخصصین فنی</span>
                </a>
                @elseif(\Illuminate\Support\Facades\Route::has('admin.users.index'))
                <a href="{{ route('admin.users.index') }}"
                   class="w-full sm:w-auto justify-center px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-2">
                    <span>👥</span>
                    <span>مدیریت کاربران</span>
                </a>
                @endif
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-5 sm:mt-6 pt-5 sm:pt-6 border-t border-slate-100 dark:border-slate-700/50">
            <a href="{{ route('admin.registration-requests.index') }}"
               class="p-3 sm:p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between gap-2 hover:border-indigo-300 dark:hover:border-indigo-500/40 transition">
                <div class="min-w-0">
                    <div class="text-[11px] sm:text-xs font-semibold text-slate-600 dark:text-slate-300 truncate">کل درخواست‌ها</div>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($counts['total']) }}</div>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base sm:text-lg shrink-0">
                    📥
                </div>
            </a>

            <a href="{{ route('admin.registration-requests.index', ['status' => 'pending']) }}"
               class="p-3 sm:p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between gap-2 hover:border-amber-400 dark:hover:border-amber-500/40 transition {{ request('status') === 'pending' ? 'ring-2 ring-amber-500/50' : '' }}">
                <div class="min-w-0">
                    <div class="text-[11px] sm:text-xs font-semibold text-slate-600 dark:text-slate-300 truncate">در انتظار بررسی</div>
                    <div class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ number_format($counts['pending']) }}</div>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center text-base sm:text-lg shrink-0">
                    ⏳
                </div>
            </a>

            <a href="{{ route('admin.registration-requests.index', ['status' => 'approved']) }}"
               class="p-3 sm:p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between gap-2 hover:border-emerald-400 dark:hover:border-emerald-500/40 transition {{ request('status') === 'approved' ? 'ring-2 ring-emerald-500/50' : '' }}">
                <div class="min-w-0">
                    <div class="text-[11px] sm:text-xs font-semibold text-slate-600 dark:text-slate-300 truncate">تایید و فعال شده</div>
                    <div class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($counts['approved']) }}</div>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base sm:text-lg shrink-0">
                    ✅
                </div>
            </a>

            <a href="{{ route('admin.registration-requests.index', ['status' => 'rejected']) }}"
               class="p-3 sm:p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between gap-2 hover:border-rose-400 dark:hover:border-rose-500/40 transition {{ request('status') === 'rejected' ? 'ring-2 ring-rose-500/50' : '' }}">
                <div class="min-w-0">
                    <div class="text-[11px] sm:text-xs font-semibold text-slate-600 dark:text-slate-300 truncate">رد شده</div>
                    <div class="text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ number_format($counts['rejected']) }}</div>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center text-base sm:text-lg shrink-0">
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
    <form method="GET" action="{{ route('admin.registration-requests.index') }}" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 p-4 shadow-sm flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
        {{-- جستجو --}}
        <div class="relative w-full lg:flex-1">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="جستجوی نام یا تلفن همراه متقاضی..."
                   class="w-full h-10 pr-10 pl-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 text-slate-400 absolute right-3.5 top-3 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:flex lg:flex-wrap items-center gap-2.5 w-full lg:w-auto">
            {{-- فیلتر نقش --}}
            <select name="role_id" onchange="this.form.submit()"
                    class="w-full lg:w-auto h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                <option value="">همه نقش‌ها</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                        {{ $role->display_name ?? $role->name }}
                    </option>
                @endforeach
            </select>

            {{-- فیلتر وضعیت --}}
            <select name="status" onchange="this.form.submit()"
                    class="w-full lg:w-auto h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                <option value="">همه وضعیت‌ها</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>فقط در انتظار بررسی ({{ $counts['pending'] }})</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>تایید شده ({{ $counts['approved'] }})</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>رد شده ({{ $counts['rejected'] }})</option>
            </select>

            <div class="flex items-center gap-2 sm:col-span-2 lg:col-span-1">
                <button type="submit"
                        class="flex-1 lg:flex-none justify-center h-10 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5">
                    <span>اعمال فیلتر</span>
                </button>

                @if(request()->hasAny(['search', 'role_id', 'status']))
                <a href="{{ route('admin.registration-requests.index') }}"
                   class="flex-1 lg:flex-none justify-center h-10 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 font-bold text-xs transition flex items-center">
                    پاکسازی
                </a>
                @endif
            </div>
        </div>
    </form>

    {{-- لیست کارت‌های درخواست --}}
    <div class="space-y-4">
        @forelse($requests as $req)
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 p-5 sm:p-6 shadow-sm hover:border-slate-300 dark:hover:border-slate-600 transition-all space-y-4">
            
            {{-- ردیف هدر کارت: آواتار، نام، نقش، وضعیت و اکشن‌ها --}}
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-700/50">
                <div class="flex items-start sm:items-center gap-3.5 min-w-0">
                    {{-- آواتار --}}
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-xl shrink-0 shadow-sm">
                        {{ mb_substr($req->name ?? 'ک', 0, 1) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white break-words">
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

                        {{-- ارتباطی: موبایل و تاریخ (بدون ایمیل) --}}
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5 text-xs text-slate-600 dark:text-slate-300">
                            @if($req->mobile)
                            <div class="flex items-center gap-1.5 font-sans font-medium text-slate-700 dark:text-slate-200" dir="ltr">
                                <span>📞</span>
                                <span>{{ $req->mobile }}</span>
                            </div>
                            @endif

                            <div class="flex items-center gap-1 text-[11px] text-slate-600 dark:text-slate-300">
                                <span>ثبت:</span>
                                <span>{{ function_exists('jdate') ? jdate($req->created_at)->format('Y/m/d - H:i') : $req->created_at->format('Y-m-d H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- دکمه‌های عملیات سریع --}}
                <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto shrink-0">
                    @if($req->status === 'pending')
                    <form action="{{ route('admin.registration-requests.approve', $req) }}" method="POST" class="flex-1 sm:flex-none">
                        @csrf
                        <button type="submit"
                                class="w-full sm:w-auto justify-center inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition active:scale-95">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>تایید و فعال‌سازی حساب</span>
                        </button>
                    </form>

                    <button type="button" onclick="openRejectModal({{ $req->id }}, '{{ addslashes($req->name) }}')"
                            class="flex-1 sm:flex-none justify-center inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-rose-50 dark:bg-slate-700/70 dark:hover:bg-rose-500/20 text-slate-700 hover:text-rose-600 dark:text-slate-200 dark:hover:text-rose-400 font-bold text-xs border border-slate-200 dark:border-slate-600 hover:border-rose-300 dark:hover:border-rose-500/30 transition active:scale-95">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>رد درخواست</span>
                    </button>
                    @elseif($req->status === 'approved')
                    <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto justify-between lg:justify-end">
                        @if($req->reviewer)
                        <div class="text-[11px] text-slate-600 dark:text-slate-300 text-right lg:text-left">
                            <span>تایید شده توسط <strong class="text-slate-800 dark:text-white">{{ $req->reviewer->name }}</strong></span>
                            @if($req->reviewed_at)
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-sans mt-0.5">
                                {{ function_exists('jdate') ? jdate($req->reviewed_at)->format('Y/m/d H:i') : $req->reviewed_at }}
                            </div>
                            @endif
                        </div>
                        @endif

                        <button type="button" onclick="openRejectModal({{ $req->id }}, '{{ addslashes($req->name) }}')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-rose-50 dark:bg-slate-700/70 dark:hover:bg-rose-500/20 text-slate-700 hover:text-rose-600 dark:text-slate-200 dark:hover:text-rose-400 font-bold text-xs border border-slate-200 dark:border-slate-600 hover:border-rose-300 dark:hover:border-rose-500/30 transition active:scale-95">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>لغو تایید / رد درخواست</span>
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            {{-- بخش اطلاعات هویتی، مکانی، سایر فیلدها و خدمات انتخابی متقاضی --}}
            @php
                $cf = is_array($req->custom_fields) ? $req->custom_fields : [];
                $province = $cf['province'] ?? null;
                $city = $cf['city'] ?? null;
                $nationalCode = $cf['national_code'] ?? null;
                $shaba = $cf['shaba_number'] ?? null;
                $address = $cf['address'] ?? null;
                $cardImage = $cf['national_card_image'] ?? null;

                $handledKeys = [
                    'province', 'city', 'national_code', 'shaba_number',
                    'address', 'national_card_image', 'categories', 'specialties',
                    'password', '_token',
                ];
                $extraCustomFields = collect($cf)->except($handledKeys)->filter(function ($val) {
                    return $val !== null && $val !== '' && (!is_array($val) || !empty($val));
                });

                // استخراج رسته‌ها و خدمات انتخابی
                $rawCats = $cf['categories'] ?? $cf['specialties'] ?? [];
                if (is_string($rawCats)) {
                    $rawCats = json_decode($rawCats, true) ?: explode(',', $rawCats);
                }
                $selectedCatIds = is_array($rawCats) ? array_values(array_unique(array_filter(array_map('intval', $rawCats)))) : [];

                // گروه‌بندی خدمات بر اساس ساختار ۳ سطحی: بخش -> دسته -> خدمت
                $groupedServices = [];
                foreach ($selectedCatIds as $catId) {
                    $cat = $categoriesMap[$catId] ?? null;
                    if ($cat) {
                        $secName = $cat->group?->section?->name ?? $cat->section?->name ?? 'سایر خدمات';
                        $grpName = $cat->group?->name ?? ($cat->section ? 'بدون دسته' : 'عمومی');
                        $groupedServices[$secName][$grpName][] = $cat;
                    } else {
                        $groupedServices['سایر خدمات']['عمومی'][] = (object) ['id' => $catId, 'name' => 'خدمت فنی #' . $catId, 'icon' => '🛠️', 'base_labor_fee' => 0];
                    }
                }
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 bg-slate-50 dark:bg-slate-900/50 p-3.5 sm:p-5 rounded-2xl border border-slate-200/70 dark:border-slate-700/50 text-xs">

                {{-- ستون ۱: مشخصات فردی و هویتی --}}
                <div class="space-y-3 min-w-0">
                    <div class="text-[11px] font-bold text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                        <span>🪪</span>
                        <span>مشخصات شناسایی و بانکی</span>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/50">
                            <span class="text-slate-600 dark:text-slate-300 shrink-0">کد ملی:</span>
                            <span class="font-bold text-slate-900 dark:text-white font-sans break-all text-left">{{ $nationalCode ?: 'ثبت نشده' }}</span>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/50">
                            <span class="text-slate-600 dark:text-slate-300 shrink-0">شماره شبا:</span>
                            <span class="font-bold text-slate-900 dark:text-white font-sans text-[11px] break-all text-left" dir="ltr">{{ $shaba ?: 'ثبت نشده' }}</span>
                        </div>
                    </div>
                </div>

                {{-- ستون ۲: محدوده و محل فعالیت --}}
                <div class="space-y-3 min-w-0">
                    <div class="text-[11px] font-bold text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                        <span>📍</span>
                        <span>موقعیت جغرافیایی و کارگاه</span>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/50">
                            <span class="text-slate-600 dark:text-slate-300 shrink-0">استان و شهر:</span>
                            <span class="font-bold text-slate-900 dark:text-white break-words text-left">
                                {{ $province ?: 'ثبت نشده' }} / {{ $city ?: 'ثبت نشده' }}
                            </span>
                        </div>

                        <div class="p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/50">
                            <span class="text-slate-600 dark:text-slate-300 block mb-0.5">آدرس کارگاه یا سکونت:</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200 leading-relaxed break-words">
                                {{ $address ?: 'آدرس دقیق ثبت نشده است.' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- ستون ۳: مدرک شناسایی / کارت ملی --}}
                <div class="space-y-3 min-w-0 md:col-span-2 lg:col-span-1">
                    <div class="text-[11px] font-bold text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
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
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">برای مشاهده در سایز اصلی کلیک نمایید</p>
                            <a href="{{ asset('storage/' . $cardImage) }}" target="_blank"
                               class="inline-flex items-center gap-1 text-[11px] text-indigo-600 dark:text-indigo-400 font-bold mt-1">
                                <span>دانلود / مشاهده فایل</span>
                                <span>↗</span>
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="p-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/50 text-center text-slate-500 dark:text-slate-400">
                        تصویر مدرک بارگذاری نشده است.
                    </div>
                    @endif
                </div>

                {{-- سایر فیلدهای داینامیک تعریف‌شده در فرم --}}
                @if($extraCustomFields->isNotEmpty())
                <div class="col-span-1 md:col-span-2 lg:col-span-3 pt-3 border-t border-slate-200/60 dark:border-slate-700/40 space-y-2.5">
                    <div class="text-[11px] font-bold text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                        <span>📝</span>
                        <span>سایر اطلاعات و مدارک ثبت‌شده</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                        @foreach($extraCustomFields as $fieldKey => $fieldVal)
                            @php
                                $fieldMeta = $customFields[$fieldKey] ?? null;
                                $fieldLabel = $fieldMeta?->label ?? $fieldKey;
                                $fieldType = strtolower($fieldMeta?->field_type ?? '');
                                $isFileField = $fieldType === 'file' || (is_string($fieldVal) && (str_starts_with($fieldVal, 'technician-docs/') || str_starts_with($fieldVal, 'custom-fields/') || preg_match('/\.(jpg|jpeg|png|webp|pdf)$/i', $fieldVal)));
                            @endphp
                            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/50 min-w-0">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 block mb-1">{{ $fieldLabel }}:</span>
                                @if($isFileField && is_string($fieldVal))
                                    <a href="{{ asset('storage/' . $fieldVal) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                        <span>مشاهده / دانلود فایل</span>
                                        <span>↗</span>
                                    </a>
                                @elseif(is_array($fieldVal))
                                    <span class="font-bold text-slate-900 dark:text-white break-words">{{ implode('، ', $fieldVal) }}</span>
                                @else
                                    <span class="font-bold text-slate-900 dark:text-white break-words">{{ $fieldVal }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- سطر تمام‌عرض: حوزه‌ها و خدمات فنی انتخابی (ساختار بخش -> دسته -> خدمت) --}}
                <div class="col-span-1 md:col-span-2 lg:col-span-3 pt-4 border-t border-slate-200/70 dark:border-slate-700/50 space-y-3.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                            <span>حوزه‌ها و خدمات فنی انتخابی متقاضی:</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/30 text-xs font-sans font-bold">
                            تعداد تخصص‌ها: {{ count($selectedCatIds) }}
                        </span>
                    </div>

                    @if(!empty($groupedServices))
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                            @foreach($groupedServices as $secName => $groups)
                                <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/70 space-y-3">
                                    <div class="text-xs sm:text-sm font-extrabold text-indigo-700 dark:text-indigo-300 pb-2 border-b border-slate-100 dark:border-slate-700/60">
                                        {{ $secName }}
                                    </div>
                                    <div class="space-y-3">
                                        @foreach($groups as $grpName => $cats)
                                            <div class="space-y-2">
                                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                                    {{ $grpName }}:
                                                </div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    @foreach($cats as $cat)
                                                        <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900/70 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 font-bold text-xs">
                                                            {{ $cat->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-xs text-slate-500 dark:text-slate-400">
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
