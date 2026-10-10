@extends('layouts.user')

@php
    $title = 'مدیریت میزبانان اقامتگاه';
    $cardClass = "bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-sm overflow-hidden transition-all duration-200";
    $badgeClass = "inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold font-sans";
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 space-y-6" x-data="hostAdminManager()">

    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-2.5 font-sans">
                <span class="w-9 h-9 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300 flex items-center justify-center shadow-inner">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </span>
                مدیریت میزبانان اقامتگاه
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mr-11 font-sans">بررسی درخواست‌های میزبانی، احراز هویت هوشمند و مدیریت وضعیت فعال‌سازی</p>
        </div>

        {{-- فیلتر و جستجو --}}
        <div class="flex items-center gap-3">
            <form action="{{ route('user.properties.hosts.admin.index') }}" method="GET" class="flex flex-wrap sm:flex-nowrap items-center gap-2.5">
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           x-model="searchQuery"
                           value="{{ request('search') }}" 
                           placeholder="جستجوی نام، اقامتگاه، شماره تماس..." 
                           class="w-64 sm:w-80 rounded-2xl border-gray-200/90 bg-gray-50/70 px-4 py-2.5 pr-9 pl-8 text-xs text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800/90 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-indigo-400 dark:focus:bg-gray-800 dark:focus:ring-indigo-500/30 font-sans shadow-sm">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 dark:text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <button type="button" 
                            x-show="searchQuery" 
                            @click="searchQuery = ''" 
                            class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200 transition"
                            title="پاک کردن جستجو">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="relative">
                    <select name="status" 
                            x-model="statusFilter"
                            onchange="this.form.submit()" 
                            class="rounded-2xl border-gray-200/90 bg-gray-50/70 px-3.5 py-2.5 text-xs text-slate-700 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800/90 dark:text-slate-200 dark:focus:border-indigo-400 dark:focus:bg-gray-800 dark:focus:ring-indigo-500/30 font-sans shadow-sm cursor-pointer">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>در انتظار تایید</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>فعال</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>معلق / رد شده</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- پیام‌های سیستمی --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center gap-2.5 font-sans">
            <svg class="w-4 h-4 flex-shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- جدول میزبان‌ها --}}
    <div class="{{ $cardClass }}">
        @if($hosts->isEmpty())
            <div class="py-16 text-center space-y-2">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-gray-100 dark:bg-gray-700/50 flex items-center justify-center text-gray-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                </div>
                <p class="text-sm font-bold text-gray-700 dark:text-gray-300 font-sans">هیچ میزبان ثبت‌شده‌ای با این مشخصات یافت نشد.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40 text-[11px] font-bold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700/80">
                        <tr>
                            <th class="px-6 py-4">میزبان / کاربر متصل</th>
                            <th class="px-6 py-4">اطلاعات تماس و شبا</th>
                            <th class="px-6 py-4 text-center">اقامتگاه‌ها</th>
                            <th class="px-6 py-4 text-center">وضعیت حساب</th>
                            <th class="px-6 py-4 text-center">احراز هویت</th>
                            <th class="px-6 py-4 text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-sans">
                        @foreach($hosts as $host)
                            @php
                                $hostJson = [
                                    'id' => $host->id,
                                    'display_name' => $host->display_name,
                                    'user_name' => optional($host->user)->name ?? 'ثبت نشده',
                                    'user_phone' => optional($host->user)->phone ?? 'ثبت نشده',
                                    'phone' => $host->phone,
                                    'avatar' => $host->avatar ? asset('storage/' . $host->avatar) : null,
                                    'about' => $host->about,
                                    'shaba_number' => $host->shaba_number,
                                    'bank_name' => $host->bank_name,
                                    'account_owner_name' => $host->account_owner_name,
                                    'national_code' => $host->national_code,
                                    'national_card_image' => $host->national_card_image ? asset('storage/' . $host->national_card_image) : null,
                                    'verification_fields' => collect($verificationFieldDefs ?? [])->map(function($f) use ($host) {
                                        $val = optional(optional($host->user)->customValues)->firstWhere('field_name', $f->field_name)->value ?? null;
                                        if (empty($val) && $f->field_name === 'national_code') {
                                            $val = $host->national_code;
                                        }
                                        if (empty($val) && $f->field_name === 'national_card_image') {
                                            $val = $host->national_card_image;
                                        }
                                        $isImage = false;
                                        $fileUrl = null;
                                        if ($f->field_type === 'file' && !empty($val)) {
                                            $fileUrl = asset('storage/' . $val);
                                            $ext = strtolower(pathinfo($val, PATHINFO_EXTENSION));
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                                        }
                                        return [
                                            'name' => $f->field_name,
                                            'label' => $f->label ?? $f->field_name,
                                            'type' => $f->field_type,
                                            'value' => $val,
                                            'file_url' => $fileUrl,
                                            'is_image' => $isImage,
                                            'options' => $f->options ?? [],
                                        ];
                                    })->values(),
                                    'kyc_status' => $host->kyc_status,
                                    'kyc_rejection_reason' => $host->kyc_rejection_reason,
                                    'status' => $host->status,
                                    'commission_rate' => $host->effective_commission_rate,
                                    'raw_commission_rate' => $host->commission_rate,
                                    'created_at' => \Morilog\Jalali\Jalalian::fromCarbon($host->created_at)->format('Y/m/d H:i'),
                                    'properties_count' => $host->properties_count,
                                    'properties' => $host->properties->map(function($p) {
                                        return [
                                            'id' => $p->id,
                                            'title' => $p->title,
                                            'code' => $p->code,
                                            'approval_status' => $p->approval_status,
                                            'publication_status' => $p->publication_status,
                                            'price' => $p->price,
                                        ];
                                    }),
                                    'searchable_meta' => mb_strtolower(implode(' ', array_filter([
                                        $host->display_name,
                                        $host->phone,
                                        $host->national_code,
                                        optional($host->user)->name,
                                        optional($host->user)->phone,
                                        $host->properties->pluck('title')->implode(' '),
                                        $host->properties->pluck('code')->implode(' '),
                                    ]))),
                                    'update_url' => route('user.properties.hosts.admin.update', $host),
                                    'approve_url' => route('user.properties.hosts.admin.approve', $host),
                                    'approve_kyc_url' => route('user.properties.hosts.admin.approve-kyc', $host),
                                    'reject_url' => route('user.properties.hosts.admin.reject', $host),
                                ];
                            @endphp

                            <tr x-init="registerHost({{ json_encode($hostJson['searchable_meta']) }})"
                                x-show="matchesSearch({{ json_encode($hostJson['searchable_meta']) }})"
                                class="hover:bg-gray-50/70 dark:hover:bg-gray-700/20 transition-colors">
                                {{-- ستون میزبان --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 flex items-center justify-center font-black text-xs flex-shrink-0 overflow-hidden shadow-inner border border-indigo-100 dark:border-indigo-800/50">
                                            @if($host->avatar)
                                                <img src="{{ asset('storage/' . $host->avatar) }}" class="w-full h-full object-cover">
                                            @else
                                                {{ mb_substr($host->display_name, 0, 1) }}
                                            @endif
                                        </div>
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-gray-900 dark:text-white">{{ $host->display_name }}</span>
                                                @if($host->kyc_status === 'approved')
                                                    <span title="احراز هویت شده">
                                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="text-[11px] text-gray-400 mt-0.5">کاربر: {{ optional($host->user)->name ?? '—' }} ({{ optional($host->user)->phone ?? 'بدون شماره' }})</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- ستون اطلاعات تماس --}}
                                <td class="px-6 py-4 text-xs font-sans text-gray-700 dark:text-gray-300">
                                    <div class="font-bold dir-ltr text-right inline-block">{{ $host->phone }}</div>
                                    @if($host->shaba_number)
                                        <div class="text-[11px] text-gray-400 mt-0.5 dir-ltr text-right">IR {{ chunk_split($host->shaba_number, 4, ' ') }}</div>
                                    @endif
                                </td>

                                {{-- ستون تعداد اقامتگاه --}}
                                <td class="px-6 py-4 font-sans text-center">
                                    <span class="px-2.5 py-1 rounded-xl bg-gray-100 dark:bg-gray-700/60 text-gray-800 dark:text-gray-200 font-bold text-xs">
                                        {{ $host->properties_count }} ملک
                                    </span>
                                </td>

                                {{-- ستون وضعیت حساب --}}
                                <td class="px-6 py-4 text-center">
                                    @if($host->status === 'active')
                                        <span class="{{ $badgeClass }} bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40">فعال</span>
                                    @elseif($host->status === 'pending')
                                        <span class="{{ $badgeClass }} bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40">در انتظار تایید</span>
                                    @else
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="{{ $badgeClass }} bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 border border-red-200 dark:border-red-800/40">معلق / رد شده</span>
                                            @if($host->kyc_rejection_reason)
                                                <span class="text-[10px] text-red-500 max-w-[140px] truncate" title="{{ $host->kyc_rejection_reason }}">علت: {{ $host->kyc_rejection_reason }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                {{-- ستون احراز هویت --}}
                                <td class="px-6 py-4 text-center">
                                    @if($host->kyc_status === 'approved')
                                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">تأیید شده</span>
                                    @elseif($host->kyc_status === 'pending')
                                        <span class="text-xs text-amber-600 dark:text-amber-400 font-bold">مدارک ارسال شده</span>
                                    @elseif($host->kyc_status === 'rejected')
                                        <span class="text-xs text-red-600 dark:text-red-400 font-bold">مدارک رد شده</span>
                                    @else
                                        <span class="text-xs text-gray-400">ثبت نشده</span>
                                    @endif
                                </td>

                                {{-- ستون عملیات --}}
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- دکمه مشاهده جزئیات --}}
                                        <button type="button" 
                                                @click="openDetailsModal({{ json_encode($hostJson) }})"
                                                class="px-2.5 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:hover:bg-indigo-500/25 dark:text-indigo-300 dark:border dark:border-indigo-500/30 font-bold text-xs transition flex items-center gap-1"
                                                title="مشاهده جزئیات کامل و مدارک">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            <span class="hidden md:inline">جزئیات</span>
                                        </button>

                                        {{-- دکمه ویرایش اطلاعات میزبان --}}
                                        <button type="button" 
                                                @click="openEditModal({{ json_encode($hostJson) }})"
                                                class="px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:hover:bg-amber-500/25 dark:text-amber-300 dark:border dark:border-amber-500/30 font-bold text-xs transition flex items-center gap-1"
                                                title="ویرایش کامل اطلاعات و وضعیت میزبان">
                                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            <span class="hidden md:inline">ویرایش</span>
                                        </button>

                                        {{-- دکمه تایید حساب در صورت غیرفعال بودن --}}
                                        @if($host->status !== 'active')
                                            <form action="{{ route('user.properties.hosts.admin.approve', $host) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-500/15 dark:hover:bg-emerald-500/25 dark:text-emerald-300 dark:border dark:border-emerald-500/30 font-bold text-xs transition flex items-center gap-1" title="تأیید و فعال‌سازی میزبان">
                                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                    <span class="hidden md:inline">تأیید میزبان</span>
                                                </button>
                                            </form>
                                        @elseif($host->kyc_status === 'pending')
                                            {{-- دکمه تایید اختصاصی مدارک هویتی در صورتی که حساب فعال است اما مدارک در صف بررسی است --}}
                                            <form action="{{ route('user.properties.hosts.admin.approve-kyc', $host) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-teal-50 text-teal-700 hover:bg-teal-100 dark:bg-teal-500/15 dark:hover:bg-teal-500/25 dark:text-teal-300 dark:border dark:border-teal-500/30 font-bold text-xs transition flex items-center gap-1" title="تأیید مدارک احراز هویت">
                                                    <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                                    <span class="hidden md:inline">تأیید مدارک</span>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- دکمه رد / تعلیق با دلیل --}}
                                        <button type="button" 
                                                @click="openRejectModal({{ json_encode($hostJson) }})"
                                                class="px-2.5 py-1.5 rounded-xl bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-500/15 dark:hover:bg-red-500/25 dark:text-red-300 dark:border dark:border-red-500/30 font-bold text-xs transition flex items-center gap-1"
                                                title="{{ $host->status === 'active' ? ($host->kyc_status === 'pending' ? 'عدم تأیید مدارک / تعلیق' : 'تعلیق حساب میزبان') : 'عدم تأیید / رد درخواست' }}">
                                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            <span class="hidden md:inline">{{ $host->status === 'active' ? ($host->kyc_status === 'pending' ? 'رد مدارک' : 'تعلیق') : 'عدم تأیید' }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        {{-- ردیف عدم تطابق در جستجوی زنده --}}
                        <tr x-show="visibleRowsCount === 0 && searchQuery.trim() !== ''" style="display: none;">
                            <td colspan="6" class="py-12 text-center">
                                <div class="w-10 h-10 mx-auto rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-slate-400 mb-2">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </div>
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300">هیچ میزبانی با عبارت جستجوی «<span x-text="searchQuery"></span>» یافت نشد.</p>
                                <button type="button" @click="searchQuery = ''" class="mt-2 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                    پاک کردن فیلتر جستجو
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                {{ $hosts->links() }}
            </div>
        @endif
    </div>

    {{-- ======================================================== --}}
    {{-- ۱. مودال جامع مشاهده جزئیات میزبان (Host Details Modal) --}}
    {{-- ======================================================== --}}
    <div x-show="showDetailsModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            {{-- پس‌زمینه مات --}}
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-sm transition-opacity" @click="showDetailsModal = false"></div>

            {{-- بدنه مودال --}}
            <div class="inline-block w-full max-w-3xl my-8 text-right align-middle transition-all transform bg-white dark:bg-gray-900 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden relative z-10 font-sans"
                 x-show="showDetailsModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                {{-- سربرگ مودال (هماهنگ با بنر پروفایل و برند سازمان) --}}
                <div class="relative overflow-hidden p-6 bg-gradient-to-br from-indigo-900 via-indigo-800 to-slate-900 dark:from-indigo-950 dark:via-slate-900 dark:to-gray-950 text-white border-b border-indigo-700/30 dark:border-gray-800">
                    {{-- افکت نوری محو پشت سربرگ --}}
                    <div class="absolute -top-16 -left-16 w-48 h-48 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="relative z-10 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-white/10 dark:bg-white/5 backdrop-blur-md border border-white/20 dark:border-white/10 text-white flex items-center justify-center font-black text-xl overflow-hidden shadow-lg flex-shrink-0">
                                <template x-if="selectedHost?.avatar">
                                    <img :src="selectedHost.avatar" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!selectedHost?.avatar">
                                    <span x-text="selectedHost?.display_name ? selectedHost.display_name.charAt(0) : '؟'"></span>
                                </template>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base font-black text-white" x-text="selectedHost?.display_name"></h3>
                                    <template x-if="selectedHost?.status === 'active'">
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-[11px] font-bold">فعال</span>
                                    </template>
                                    <template x-if="selectedHost?.status === 'pending'">
                                        <span class="px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-400/30 text-[11px] font-bold">در انتظار بررسی</span>
                                    </template>
                                    <template x-if="selectedHost?.status === 'suspended'">
                                        <span class="px-2.5 py-0.5 rounded-full bg-red-500/20 text-red-300 border border-red-400/30 text-[11px] font-bold">معلق / رد شده</span>
                                    </template>
                                </div>
                                <p class="text-xs text-indigo-200/90 dark:text-slate-300 mt-1 flex items-center gap-3">
                                    <span>تلفن هماهنگی: <span class="dir-ltr inline-block font-bold" x-text="selectedHost?.phone"></span></span>
                                    <span>•</span>
                                    <span>تاریخ عضویت: <span class="font-bold" x-text="selectedHost?.created_at"></span></span>
                                </p>
                            </div>
                        </div>

                        <button @click="showDetailsModal = false" class="p-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white/80 hover:text-white transition focus:outline-none">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                {{-- محتوای مودال --}}
                <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto bg-gray-50/50 dark:bg-gray-900/50">

                    {{-- بخش علت رد قبلی اگر وجود دارد --}}
                    <template x-if="selectedHost?.kyc_rejection_reason">
                        <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 text-xs text-red-800 dark:text-red-200 space-y-1">
                            <div class="font-bold flex items-center gap-2 text-red-900 dark:text-red-300">
                                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                آخرین علت ثبت شده برای عدم تأیید / تعلیق:
                            </div>
                            <p class="mr-6 leading-relaxed text-slate-700 dark:text-slate-200 font-sans" x-text="selectedHost.kyc_rejection_reason"></p>
                        </div>
                    </template>

                    {{-- شبکه اطلاعات اصلی (Bento Info Grid) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- کارت اطلاعات کاربری و تماس --}}
                        <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 shadow-sm space-y-3">
                            <h4 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                حساب کاربری و تماس
                            </h4>
                            <div class="space-y-2.5 text-xs">
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-300">
                                    <span>نام کاربر در سامانه:</span>
                                    <span class="font-bold text-gray-900 dark:text-gray-100" x-text="selectedHost?.user_name"></span>
                                </div>
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-300">
                                    <span>شماره همراه کاربر:</span>
                                    <span class="font-bold text-gray-900 dark:text-gray-100 dir-ltr font-sans" x-text="selectedHost?.user_phone || 'ثبت نشده'"></span>
                                </div>
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-300">
                                    <span>کارمزد پلتفرم:</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400" x-text="selectedHost?.commission_rate + '٪'"></span>
                                </div>
                            </div>
                        </div>

                        {{-- کارت اطلاعات بانکی و شبا --}}
                        <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 shadow-sm space-y-3">
                            <h4 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                مشخصات بانکی و تسویه
                            </h4>
                            <div class="space-y-2.5 text-xs">
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-300">
                                    <span>نام بانک:</span>
                                    <span class="font-bold text-gray-900 dark:text-gray-100" x-text="selectedHost?.bank_name || 'ثبت نشده'"></span>
                                </div>
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-300">
                                    <span>صاحب حساب:</span>
                                    <span class="font-bold text-gray-900 dark:text-gray-100" x-text="selectedHost?.account_owner_name || 'ثبت نشده'"></span>
                                </div>
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-300">
                                    <span>شماره شبا:</span>
                                    <span class="font-bold dir-ltr text-gray-900 dark:text-gray-100" x-text="selectedHost?.shaba_number ? 'IR ' + selectedHost.shaba_number : 'ثبت نشده'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- کارت احراز هویت و مدارک هویتی (KYC) پویا --}}
                    <div class="p-5 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black text-indigo-950 dark:text-indigo-200 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                مدارک و فیلدهای احراز هویت میزبان
                            </h4>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-bold font-sans"
                                  :class="{
                                      'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20': selectedHost?.kyc_status === 'approved',
                                      'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20': selectedHost?.kyc_status === 'pending',
                                      'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20': selectedHost?.kyc_status === 'rejected',
                                      'bg-gray-100 dark:bg-gray-700 text-gray-500': !selectedHost?.kyc_status || selectedHost?.kyc_status === 'not_submitted'
                                  }"
                                  x-text="selectedHost?.kyc_status === 'approved' ? 'تأیید شده' : (selectedHost?.kyc_status === 'pending' ? 'در انتظار بررسی' : (selectedHost?.kyc_status === 'rejected' ? 'رد شده' : 'ثبت نشده'))"></span>
                        </div>

                        {{-- نمایش لیست فیلدها و مدارک احراز هویت داینامیک --}}
                        <template x-if="selectedHost?.verification_fields && selectedHost.verification_fields.length > 0">
                            <div class="space-y-3">
                                <template x-for="vf in selectedHost.verification_fields" :key="vf.name">
                                    <div class="border border-indigo-100/80 dark:border-indigo-900/40 rounded-2xl p-4 bg-white dark:bg-gray-800/90 shadow-sm space-y-2">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-bold text-gray-900 dark:text-white" x-text="vf.label"></span>
                                            <template x-if="vf.type !== 'file'">
                                                <span class="font-bold dir-ltr text-indigo-700 dark:text-indigo-300 font-sans" x-text="vf.value || 'ثبت نشده'"></span>
                                            </template>
                                        </div>

                                        {{-- اگر فیلد از نوع فایل / تصویر باشد --}}
                                        <template x-if="vf.type === 'file'">
                                            <div>
                                                <template x-if="vf.value && vf.is_image">
                                                    <div class="space-y-2 text-center pt-1">
                                                        <div class="relative inline-block group">
                                                            <img :src="vf.file_url" class="max-h-52 mx-auto rounded-xl object-contain border border-gray-200 dark:border-gray-700 shadow-sm transition hover:brightness-95">
                                                            <a :href="vf.file_url" target="_blank" class="absolute bottom-2 left-2 px-3 py-1.5 rounded-lg bg-gray-900/80 hover:bg-gray-900 text-white text-[11px] font-bold flex items-center gap-1.5 backdrop-blur-sm transition">
                                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                                                مشاهده سایز اصلی
                                                            </a>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="vf.value && !vf.is_image">
                                                    <div class="flex items-center justify-between pt-1">
                                                        <span class="text-xs text-slate-500 dark:text-slate-400">فایل ضمیمه بارگذاری شده</span>
                                                        <a :href="vf.file_url" target="_blank" class="px-3 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-bold hover:underline">
                                                            دانلود / مشاهده فایل
                                                        </a>
                                                    </div>
                                                </template>
                                                <template x-if="!vf.value">
                                                    <div class="py-3 text-center text-slate-400 text-xs">
                                                        مدرکی برای این فیلد بارگذاری نشده است.
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- در صورتی که فیلدی تعریف نشده باشد --}}
                        <template x-if="!selectedHost?.verification_fields || selectedHost.verification_fields.length === 0">
                            <div class="border border-indigo-100/80 dark:border-indigo-900/40 rounded-2xl p-4 bg-white dark:bg-gray-800/90 text-center shadow-inner">
                                <div class="py-4 text-slate-500 dark:text-slate-400 text-xs font-sans">
                                    فیلد احراز هویت سفارشی تعریف نشده است.
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- متن درباره میزبان --}}
                    <template x-if="selectedHost?.about">
                        <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 shadow-sm space-y-1.5">
                            <h4 class="text-xs font-black text-gray-900 dark:text-white">درباره میزبان و سوابق:</h4>
                            <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed font-sans" x-text="selectedHost.about"></p>
                        </div>
                    </template>

                    {{-- لیست اقامتگاه‌های ثبت شده توسط میزبان --}}
                    <div class="space-y-3">
                        <h4 class="text-xs font-black text-gray-900 dark:text-white flex items-center justify-between">
                            <span>اقامتگاه‌های ثبت شده میزبان</span>
                            <span class="text-slate-500 dark:text-slate-400 font-normal" x-text="'مجموعاً ' + (selectedHost?.properties_count || 0) + ' اقامتگاه'"></span>
                        </h4>

                        <div class="border border-gray-200/80 dark:border-gray-750/80 rounded-2xl overflow-hidden divide-y divide-gray-100 dark:divide-gray-700/60 text-xs shadow-sm">
                            <template x-if="selectedHost?.properties && selectedHost.properties.length > 0">
                                <div>
                                    <template x-for="prop in selectedHost.properties" :key="prop.id">
                                        <div class="p-3 bg-white dark:bg-gray-800/90 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-gray-900 dark:text-white" x-text="prop.title"></span>
                                                <span class="text-[11px] text-slate-500 dark:text-slate-400" x-text="'(کد: ' + prop.code + ')'"></span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <template x-if="prop.approval_status === 'approved'">
                                                    <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border dark:border-emerald-800/40 text-[10px] font-bold">تأیید شده</span>
                                                </template>
                                                <template x-if="prop.approval_status === 'pending_review'">
                                                    <span class="px-2 py-0.5 rounded-lg bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 dark:border dark:border-amber-800/40 text-[10px] font-bold">در انتظار بازبینی</span>
                                                </template>
                                                <template x-if="prop.approval_status === 'rejected'">
                                                    <span class="px-2 py-0.5 rounded-lg bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 dark:border dark:border-red-800/40 text-[10px] font-bold">رد شده</span>
                                                </template>
                                                <a :href="'/properties/' + (prop.code || prop.id)" target="_blank" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 text-[11px] font-bold">نمایش</a>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!selectedHost?.properties || selectedHost.properties.length === 0">
                                <div class="p-4 text-center text-slate-500 dark:text-slate-400 text-xs bg-white dark:bg-gray-800/60">
                                    هنوز اقامتگاهی توسط این میزبان ثبت نشده است.
                                </div>
                            </template>
                        </div>
                    </div>

                </div>

                {{-- پاورقی و دکمه‌های اقدام مستقیم در مودال --}}
                <div class="p-5 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/90 flex items-center justify-between">
                    <button type="button" @click="showDetailsModal = false" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        بستن پنجره
                    </button>

                    <div class="flex items-center gap-2">
                        {{-- دکمه ویرایش اطلاعات میزبان --}}
                        <button type="button" 
                                @click="showDetailsModal = false; openEditModal(selectedHost)"
                                class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            <span>ویرایش اطلاعات</span>
                        </button>
                        {{-- دکمه فعال‌سازی کل حساب میزبان --}}
                        <template x-if="selectedHost?.status !== 'active'">
                            <form :action="selectedHost?.approve_url" method="POST">
                                @csrf
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    تأیید و فعال‌سازی میزبان
                                </button>
                            </form>
                        </template>

                        {{-- دکمه تایید اختصاصی مدارک در صورتی که حساب فعال است اما مدارک در صف بررسی است --}}
                        <template x-if="selectedHost?.status === 'active' && selectedHost?.kyc_status === 'pending'">
                            <form :action="selectedHost?.approve_kyc_url" method="POST">
                                @csrf
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-600/20 transition flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                    تأیید مدارک احراز هویت
                                </button>
                            </form>
                        </template>

                        <button type="button" 
                                @click="showDetailsModal = false; openRejectModal(selectedHost)"
                                class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-md shadow-red-600/20 transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            <span x-text="selectedHost?.status === 'active' ? (selectedHost?.kyc_status === 'pending' ? 'عدم تأیید مدارک با دلیل' : 'تعلیق حساب میزبان') : 'عدم تأیید مدارک با دلیل'"></span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- ۲. مودال جامع ویرایش اطلاعات میزبان (Host Edit Modal) --}}
    {{-- ======================================================== --}}
    <div x-show="showEditModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            {{-- پس‌زمینه مات --}}
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-sm transition-opacity" @click="showEditModal = false"></div>

            {{-- بدنه مودال --}}
            <div class="inline-block w-full max-w-4xl my-8 text-right align-middle transition-all transform bg-white dark:bg-gray-900 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden relative z-10 font-sans"
                 x-show="showEditModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                {{-- فرم ارسال ویرایش --}}
                <form :action="editingHost?.update_url" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- سربرگ مودال ویرایش --}}
                    <div class="relative overflow-hidden p-6 bg-gradient-to-br from-indigo-900 via-indigo-800 to-slate-900 dark:from-indigo-950 dark:via-slate-900 dark:to-gray-950 text-white border-b border-indigo-700/30 dark:border-gray-800">
                        <div class="absolute -top-16 -left-16 w-48 h-48 bg-amber-500/20 rounded-full blur-2xl pointer-events-none"></div>

                        <div class="relative z-10 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-white/10 dark:bg-white/5 backdrop-blur-md border border-white/20 dark:border-white/10 text-amber-300 flex items-center justify-center font-black text-lg shadow-lg flex-shrink-0">
                                    <svg class="w-6 h-6 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-base font-black text-white">ویرایش اطلاعات و تنظیمات میزبان</h3>
                                        <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 text-xs font-bold" x-text="editingHost?.display_name"></span>
                                    </div>
                                    <p class="text-xs text-indigo-200/90 dark:text-slate-300 mt-1">
                                        ویرایش مشخصات تماس، اطلاعات بانکی، فیلدهای احراز هویت و وضعیت حساب کاربری
                                    </p>
                                </div>
                            </div>

                            <button type="button" @click="showEditModal = false" class="p-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white/80 hover:text-white transition focus:outline-none">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    {{-- بدنه اسکرول‌خور مودال با ساختار Bento --}}
                    <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto bg-gray-50/50 dark:bg-gray-900/50">

                        {{-- ردیف ۱: اطلاعات پایه و حساب بانکی --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            {{-- کارت مشخصات اصلی میزبان --}}
                            <div class="p-5 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 shadow-sm space-y-4">
                                <h4 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-2">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                    مشخصات نمایشی و کارمزد
                                </h4>

                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            نام نمایشی میزبان <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               name="display_name" 
                                               x-model="editForm.display_name" 
                                               required
                                               class="w-full rounded-xl border-gray-200 bg-gray-50/60 p-2.5 text-xs text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 font-sans"
                                               placeholder="مثلاً: اقامتگاه بوم‌گردی نگین یا علی حسینی">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            تلفن تماس و هماهنگی <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               name="phone" 
                                               x-model="editForm.phone" 
                                               required
                                               class="w-full rounded-xl border-gray-200 bg-gray-50/60 p-2.5 text-xs text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 font-sans dir-ltr text-right"
                                               placeholder="0912...">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            درصد کارمزد پلتفرم (٪)
                                        </label>
                                        <input type="number" 
                                               step="0.01" 
                                               min="0" 
                                               max="100" 
                                               name="commission_rate" 
                                               x-model="editForm.commission_rate" 
                                               class="w-full rounded-xl border-gray-200 bg-gray-50/60 p-2.5 text-xs text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 font-sans dir-ltr text-right"
                                               placeholder="خالی = استفاده از پیش‌فرض سامانه">
                                        <span class="block text-[10px] text-gray-400 mt-1">در صورت خالی گذاشتن، نرخ پیش‌فرض سامانه اعمال می‌گردد.</span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            درباره میزبان و سوابق
                                        </label>
                                        <textarea name="about" 
                                                  x-model="editForm.about" 
                                                  rows="2" 
                                                  class="w-full rounded-xl border-gray-200 bg-gray-50/60 p-2.5 text-xs text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 font-sans resize-none"
                                                  placeholder="توضیحات کوتاه در مورد میزبان..."></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- کارت اطلاعات بانکی و شبا --}}
                            <div class="p-5 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 shadow-sm space-y-4">
                                <h4 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    مشخصات بانکی و تسویه حساب
                                </h4>

                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            شماره شبا (۲۴ رقم بدون IR)
                                        </label>
                                        <div class="relative">
                                            <input type="text" 
                                                   name="shaba_number" 
                                                   x-model="editForm.shaba_number" 
                                                   @input="detectEditBank()" 
                                                   maxlength="24" 
                                                   class="w-full rounded-xl border-gray-200 bg-gray-50/60 p-2.5 pl-14 text-xs text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 font-sans dir-ltr text-left tracking-wider"
                                                   placeholder="000000000000000000000000">
                                            <span class="absolute inset-y-0 left-3 flex items-center text-xs font-bold text-indigo-500 dark:text-indigo-400 font-sans">IR</span>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            نام بانک
                                        </label>
                                        <input type="text" 
                                               name="bank_name" 
                                               x-model="editForm.bank_name" 
                                               class="w-full rounded-xl border-gray-200 bg-gray-50/60 p-2.5 text-xs text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 font-sans"
                                               placeholder="نام بانک (تشخیص خودکار از روی شبا)">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            نام و نام خانوادگی صاحب حساب
                                        </label>
                                        <input type="text" 
                                               name="account_owner_name" 
                                               x-model="editForm.account_owner_name" 
                                               class="w-full rounded-xl border-gray-200 bg-gray-50/60 p-2.5 text-xs text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 font-sans"
                                               placeholder="نام دقیق صاحب حساب">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ردیف ۲: وضعیت حساب و وضعیت احراز هویت (هماهنگ با هم) --}}
                        <div class="p-5 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 space-y-4">
                            <div class="flex items-center justify-between border-b border-indigo-100 dark:border-indigo-900/50 pb-2">
                                <h4 class="text-xs font-black text-indigo-950 dark:text-indigo-200 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    وضعیت حساب کاربری و احراز هویت (هماهنگی یکپارچه)
                                </h4>
                                <span class="text-[11px] text-indigo-700 dark:text-indigo-300 font-bold">قابل کنترل مستقل توسط مدیریت</span>
                            </div>

                            {{-- راهنمای سیستم --}}
                            <div class="p-3.5 rounded-xl bg-white/80 dark:bg-gray-800/80 border border-indigo-100 dark:border-indigo-900/30 text-xs text-slate-700 dark:text-slate-300 space-y-1">
                                <div class="flex items-center gap-1.5 font-bold text-indigo-900 dark:text-indigo-300">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    قاعده هماهنگی سیستمی:
                                </div>
                                <p class="text-[11px] leading-relaxed text-slate-600 dark:text-slate-300">
                                    وضعیت حساب کاربری و وضعیت مدارک هویتی مستقل بوده و مدیریت می‌تواند هر دو را تغییر دهد. چنانچه کاربر در آینده مدارک احراز هویت خود را در پروفایل تغییر دهد، سیستم به صورت خودکار وضعیت احراز هویت را به «در صف بررسی» تغییر خواهد داد تا مجدداً بازبینی شود.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- وضعیت حساب کاربری --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        وضعیت حساب کاربری میزبان <span class="text-red-500">*</span>
                                    </label>
                                    <select name="status" 
                                            x-model="editForm.status" 
                                            required
                                            class="w-full rounded-xl border-gray-200 bg-white p-2.5 text-xs text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 font-sans font-bold">
                                        <option value="active">فعال (دارای مجوز و دسترسی به پنل)</option>
                                        <option value="pending">در انتظار بررسی اولیه</option>
                                        <option value="suspended">معلق / غیرفعال</option>
                                    </select>
                                </div>

                                {{-- وضعیت احراز هویت و مدارک --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        وضعیت احراز هویت و مدارک (KYC) <span class="text-red-500">*</span>
                                    </label>
                                    <select name="kyc_status" 
                                            x-model="editForm.kyc_status" 
                                            required
                                            class="w-full rounded-xl border-gray-200 bg-white p-2.5 text-xs text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 font-sans font-bold">
                                        <option value="approved">تأیید شده کامل</option>
                                        <option value="pending">در صف بررسی مدارک</option>
                                        <option value="rejected">مدارک رد شده</option>
                                        <option value="not_submitted">مدارک ارسال نشده</option>
                                    </select>
                                </div>
                            </div>

                            {{-- فیلد علت رد یا تعلیق --}}
                            <div x-show="editForm.status === 'suspended' || editForm.kyc_status === 'rejected'" 
                                 x-transition 
                                 class="space-y-1.5 pt-2 border-t border-indigo-100 dark:border-indigo-900/40">
                                <label class="block text-xs font-bold text-red-700 dark:text-red-300">
                                    علت عدم تأیید یا تعلیق حساب / مدارک:
                                </label>
                                <textarea name="kyc_rejection_reason" 
                                          x-model="editForm.kyc_rejection_reason" 
                                          rows="2" 
                                          class="w-full rounded-xl border-red-200 bg-red-50/50 p-2.5 text-xs text-red-900 focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-500/20 transition-all dark:border-red-900 dark:bg-red-950/30 dark:text-red-100 font-sans resize-none"
                                          placeholder="توضیح دلیل عدم تأیید یا تعلیق جهت اطلاع میزبان..."></textarea>
                            </div>
                        </div>

                        {{-- ردیف ۳: فیلدها و مدارک احراز هویت داینامیک --}}
                        <template x-if="editingHost?.verification_fields && editingHost.verification_fields.length > 0">
                            <div class="p-5 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 shadow-sm space-y-4">
                                <h4 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-2">
                                    <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                                    فیلدها و مدارک احراز هویت سفارشی
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <template x-for="vf in editingHost.verification_fields" :key="vf.name">
                                        <div class="space-y-2 p-3.5 rounded-xl bg-gray-50/80 dark:bg-gray-900/60 border border-gray-200/60 dark:border-gray-700/60">
                                            <div class="flex items-center justify-between">
                                                <label class="text-xs font-bold text-gray-900 dark:text-white" x-text="vf.label"></label>
                                                <span class="text-[10px] text-slate-400" x-text="'نوع: ' + vf.type"></span>
                                            </div>

                                            {{-- فیلدهای غیر فایل --}}
                                            <template x-if="vf.type !== 'file' && vf.type !== 'textarea' && vf.type !== 'select'">
                                                <input :type="vf.type === 'number' ? 'number' : 'text'" 
                                                       :name="'custom[' + vf.name + ']'" 
                                                       x-model="editForm.custom_fields[vf.name]" 
                                                       class="w-full rounded-xl border-gray-200 bg-white p-2 text-xs text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 font-sans">
                                            </template>

                                            {{-- فیلد چندخطی --}}
                                            <template x-if="vf.type === 'textarea'">
                                                <textarea :name="'custom[' + vf.name + ']'" 
                                                          x-model="editForm.custom_fields[vf.name]" 
                                                          rows="2" 
                                                          class="w-full rounded-xl border-gray-200 bg-white p-2 text-xs text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 font-sans resize-none"></textarea>
                                            </template>

                                            {{-- فیلد سلکت --}}
                                            <template x-if="vf.type === 'select'">
                                                <select :name="'custom[' + vf.name + ']'" 
                                                        x-model="editForm.custom_fields[vf.name]" 
                                                        class="w-full rounded-xl border-gray-200 bg-white p-2 text-xs text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 font-sans">
                                                    <option value="">انتخاب کنید...</option>
                                                    <template x-for="(optVal, optKey) in (vf.options || {})" :key="optKey">
                                                        <option :value="optKey" x-text="optVal"></option>
                                                    </template>
                                                </select>
                                            </template>

                                            {{-- فیلد فایل / مدرک تصویری --}}
                                            <template x-if="vf.type === 'file'">
                                                <div class="space-y-2 pt-1">
                                                    <template x-if="vf.file_url">
                                                        <div class="flex items-center justify-between p-2 rounded-lg bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/40 text-xs">
                                                            <div class="flex items-center gap-2">
                                                                <template x-if="vf.is_image">
                                                                    <img :src="vf.file_url" class="w-8 h-8 rounded object-cover border border-gray-200 dark:border-gray-700">
                                                                </template>
                                                                <span class="text-[11px] text-slate-700 dark:text-slate-300 font-bold">مدرک فعلی ثبت شده</span>
                                                            </div>
                                                            <a :href="vf.file_url" target="_blank" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline text-[11px]">مشاهده فایل</a>
                                                        </div>
                                                    </template>

                                                    <div class="space-y-1">
                                                        <label class="block text-[11px] text-slate-500 dark:text-slate-400">
                                                            بارگذاری فایل جدید / جایگزین:
                                                        </label>
                                                        <input type="file" 
                                                               :name="'custom[' + vf.name + ']'" 
                                                               class="w-full text-xs text-slate-500 file:mr-0 file:ml-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 hover:file:bg-indigo-100 font-sans">
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                    </div>

                    {{-- فوتر مودال ویرایش --}}
                    <div class="p-5 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/90 flex items-center justify-end gap-3">
                        <button type="button" 
                                @click="showEditModal = false" 
                                class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                            انصراف
                        </button>
                        <button type="submit" 
                                class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span>ذخیره کلیه تغییرات</span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- ۳. مودال ثبت دلیل عدم تأیید / تعلیق (Reject Reason Modal) --}}
    {{-- ======================================================== --}}
    <div x-show="showRejectModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            {{-- پس‌زمینه مات --}}
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-sm transition-opacity" @click="showRejectModal = false"></div>

            {{-- بدنه مودال --}}
            <div class="inline-block w-full max-w-lg my-8 text-right align-middle transition-all transform bg-white dark:bg-gray-900 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden relative z-10 font-sans"
                 x-show="showRejectModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                {{-- فرم ارسال عدم تایید --}}
                <form :action="targetHost?.reject_url" method="POST" class="space-y-5 p-6">
                    @csrf

                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-900/50 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900 dark:text-white">
                                    <span x-text="targetHost?.status === 'active' ? (targetHost?.kyc_status === 'pending' ? 'عدم تأیید مدارک احراز هویت' : 'تعلیق حساب میزبان') : 'عدم تأیید درخواست میزبانی'"></span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">میزبان: <span class="font-bold text-gray-900 dark:text-gray-100" x-text="targetHost?.display_name"></span></p>
                            </div>
                        </div>

                        <button type="button" @click="showRejectModal = false" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    {{-- برچسب‌های دلایل آماده و پرتکرار جهت تسریع کار --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            انتخاب سریع علت‌های پرتکرار:
                        </label>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" 
                                    @click="rejectionReason = 'تصویر کارت ملی ناخوانا، تار یا ناقص است. لطفاً تصویر باکیفیت بارگذاری فرمایید.'" 
                                    class="px-2.5 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700/80 text-slate-700 dark:text-slate-300 text-[11px] border border-gray-200/50 dark:border-gray-700/60 transition">
                                تصویر کارت ملی ناخوانا
                            </button>
                            <button type="button" 
                                    @click="rejectionReason = 'کد ملی هوشمند وارد شده نامعتبر بوده یا با مشخصات هویتی کارت ملی مطابقت ندارد.'" 
                                    class="px-2.5 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700/80 text-slate-700 dark:text-slate-300 text-[11px] border border-gray-200/50 dark:border-gray-700/60 transition">
                                مغایرت کد ملی
                            </button>
                            <button type="button" 
                                    @click="rejectionReason = 'اطلاعات شماره شبا با نام صاحب حساب و کد ملی ثبت شده مطابقت ندارد.'" 
                                    class="px-2.5 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700/80 text-slate-700 dark:text-slate-300 text-[11px] border border-gray-200/50 dark:border-gray-700/60 transition">
                                عدم تطابق شبا بانکی
                            </button>
                            <button type="button" 
                                    @click="rejectionReason = 'نقض قوانین پلتفرم یا عدم پاسخگویی در شماره تماس هماهنگی.'" 
                                    class="px-2.5 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700/80 text-slate-700 dark:text-slate-300 text-[11px] border border-gray-200/50 dark:border-gray-700/60 transition">
                                نقض قوانین پلتفرم
                            </button>
                        </div>
                    </div>

                    {{-- فیلد متنی دلیل عدم تایید --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            علت عدم تایید / تعلیق (به میزبان نمایش داده می‌شود) <span class="text-red-500">*</span>
                        </label>
                        <textarea name="reason" 
                                  x-model="rejectionReason"
                                  rows="4" 
                                  required 
                                  class="w-full rounded-2xl border-gray-200 bg-gray-50/70 p-3.5 text-xs text-gray-900 focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-800/90 placeholder-gray-400 dark:placeholder-gray-500 font-sans resize-none"
                                  placeholder="دلیل عدم تایید یا تعلیق را به‌طور دقیق بنویسید تا میزبان بتواند آن را اصلاح کند..."></textarea>
                    </div>

                    {{-- دکمه‌های عملیات --}}
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="showRejectModal = false" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                            انصراف
                        </button>
                        <button type="submit" 
                                :disabled="!rejectionReason.trim()"
                                class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-bold text-xs shadow-md shadow-red-600/20 transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <span>ثبت علت و تعلیق حساب</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>

<script>
    function hostAdminManager() {
        return {
            showDetailsModal: false,
            showRejectModal: false,
            showEditModal: false,
            selectedHost: null,
            targetHost: null,
            editingHost: null,
            rejectionReason: '',
            editForm: {
                display_name: '',
                phone: '',
                commission_rate: '',
                about: '',
                shaba_number: '',
                bank_name: '',
                account_owner_name: '',
                status: 'pending',
                kyc_status: 'pending',
                kyc_rejection_reason: '',
                custom_fields: {}
            },
            searchQuery: '{{ addslashes(request('search', '')) }}',
            statusFilter: '{{ addslashes(request('status', '')) }}',
            searchTimeout: null,
            hostMetas: [],

            init() {
                this.$watch('searchQuery', (val) => {
                    clearTimeout(this.searchTimeout);
                    // هماهنگی سرچ زنده با سرور برای زمانی که نیاز به صفحه‌بندی کامل یا واکشی از کل دیتابیس است
                    this.searchTimeout = setTimeout(() => {
                        const url = new URL(window.location.href);
                        if (val.trim()) {
                            url.searchParams.set('search', val.trim());
                        } else {
                            url.searchParams.delete('search');
                        }
                        url.searchParams.delete('page');
                        window.history.replaceState({}, '', url.toString());
                    }, 500);
                });
            },

            registerHost(meta) {
                if (meta && !this.hostMetas.includes(meta)) {
                    this.hostMetas.push(meta);
                }
            },

            matchesSearch(metaString) {
                if (!this.searchQuery || !this.searchQuery.trim()) {
                    return true;
                }
                const q = this.searchQuery.trim().toLowerCase();
                // تفکیک کلمات برای جستجوی دقیق‌تر چندکلمه‌ای بر اساس نام، نام اقامتگاه، شماره تماس
                const terms = q.split(/\s+/).filter(Boolean);
                const target = (metaString || '').toLowerCase();
                return terms.every(term => target.includes(term));
            },

            get visibleRowsCount() {
                if (!this.searchQuery || !this.searchQuery.trim()) {
                    return this.hostMetas.length;
                }
                return this.hostMetas.filter(meta => this.matchesSearch(meta)).length;
            },

            openDetailsModal(host) {
                this.selectedHost = host;
                this.showDetailsModal = true;
            },

            openRejectModal(host) {
                this.targetHost = host;
                this.rejectionReason = host.kyc_rejection_reason || '';
                this.showRejectModal = true;
            },

            openEditModal(host) {
                this.editingHost = host;
                this.editForm = {
                    display_name: host.display_name || '',
                    phone: host.phone || '',
                    commission_rate: (host.raw_commission_rate !== null && host.raw_commission_rate !== undefined) ? host.raw_commission_rate : '',
                    about: host.about || '',
                    shaba_number: host.shaba_number || '',
                    bank_name: host.bank_name || '',
                    account_owner_name: host.account_owner_name || '',
                    status: host.status || 'pending',
                    kyc_status: host.kyc_status || 'pending',
                    kyc_rejection_reason: host.kyc_rejection_reason || '',
                    custom_fields: {}
                };

                if (host.verification_fields && Array.isArray(host.verification_fields)) {
                    host.verification_fields.forEach(vf => {
                        this.editForm.custom_fields[vf.name] = (vf.value !== null && vf.value !== undefined) ? vf.value : '';
                    });
                }

                this.showEditModal = true;
            },

            detectEditBank() {
                if (!this.editForm.shaba_number) return;
                this.editForm.shaba_number = this.editForm.shaba_number.replace(/\D/g, '');

                const code = this.editForm.shaba_number.substring(2, 5);
                const bankMap = {
                    '012': 'بانک ملت',
                    '017': 'بانک ملی ایران',
                    '018': 'بانک تجارت',
                    '019': 'بانک صادرات ایران',
                    '013': 'بانک رفاه کارگران',
                    '056': 'بانک سامان',
                    '054': 'بانک پارسیان',
                    '055': 'بانک اقتصاد نوین',
                    '057': 'بانک پاسارگاد',
                    '061': 'بانک شهر',
                    '058': 'بانک سرمایه',
                    '059': 'بانک سینا',
                    '062': 'بانک آینده',
                    '051': 'بانک موسسه اعتباری توسعه',
                    '053': 'بانک کارآفرین',
                    '020': 'بانک توسعه صادرات',
                    '021': 'پست بانک ایران',
                    '022': 'بانک توسعه تعاون',
                    '014': 'بانک مسکن',
                    '016': 'بانک کشاورزی',
                    '011': 'بانک صنعت و معدن',
                    '015': 'بانک سپه',
                };

                if (bankMap[code] && !this.editForm.bank_name) {
                    this.editForm.bank_name = bankMap[code];
                }
            }
        };
    }
</script>
@endsection
