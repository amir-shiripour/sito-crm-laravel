@extends('layouts.user')

@php
    $title = 'مدیریت فروشندگان';
@endphp

@section('content')
    <div class="space-y-4">
        {{-- هدر و ابزارها --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    مدیریت فروشندگان (Vendors)
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    در این بخش می‌توانید فروشگاه‌ها و وضعیت احراز هویت آن‌ها را مدیریت کنید.
                </p>
            </div>

            <div class="flex items-center gap-3 self-end sm:self-auto">
                @can('market.vendors.manage')
                    <a href="{{ route('user.market.vendors.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        ثبت فروشگاه دستی
                    </a>
                @endcan
            </div>
        </div>

        {{-- پنل فیلتر پیشرفته (استایل‌ها اصلاح شد) --}}
        {{-- پنل فیلتر پیشرفته --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-300">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                    فیلترهای پیشرفته
                </h2>
                @if(request()->except('page'))
                    <a href="{{ route('user.market.vendors.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        حذف فیلترها
                    </a>
                @endif
            </div>
            <div class="p-5">
                <form action="{{ route('user.market.vendors.index') }}" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

                        {{-- جستجوی متنی --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">جستجو</label>
                            <div class="relative">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="نام فروشگاه، کدملی..."
                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </div>
                            </div>
                        </div>

                        {{-- فیلتر احراز هویت --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">وضعیت احراز هویت</label>
                            <div class="relative">
                                <select name="kyc_status" class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                                    <option value="">همه موارد</option>
                                    <option value="pending" @selected(request('kyc_status') === 'pending')>در حال بررسی</option>
                                    <option value="approved" @selected(request('kyc_status') === 'approved')>تایید شده</option>
                                    <option value="rejected" @selected(request('kyc_status') === 'rejected')>رد شده / ناقص</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </div>
                        </div>

                        {{-- فیلتر فعالیت --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">وضعیت فعالیت</label>
                            <div class="relative">
                                <select name="status" class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                                    <option value="">همه موارد</option>
                                    <option value="active" @selected(request('status') === 'active')>فعال</option>
                                    <option value="pending" @selected(request('status') === 'pending')>تعلیق / بررسی</option>
                                    <option value="suspended" @selected(request('status') === 'suspended')>مسدود</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="col-span-1 sm:col-span-3 flex items-center justify-end gap-4 pt-4 border-t border-gray-100 dark:border-gray-700 mt-5">
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 dark:focus:ring-indigo-900 transition-all shadow-lg shadow-indigo-500/30 active:scale-95">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                            اعمال فیلتر
                        </button>
                    </div>
                </form>
            </div>
        </div>
        {{-- جدول فروشندگان --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">#</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">نام فروشگاه</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">کاربر (مالک)</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">احراز هویت (KYC)</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">وضعیت فروشگاه</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($vendors as $vendor)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-150">
                            <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ $vendor->id }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $vendor->store_name }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 dir-ltr text-right">{{ $vendor->slug }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ optional($vendor->user)->name ?? 'کاربر حذف شده' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $kycStatuses = [
                                        'approved' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-600 dark:text-emerald-400', 'label' => 'تایید شده', 'icon' => 'M5 13l4 4L19 7'],
                                        'pending' => ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'text' => 'text-amber-600 dark:text-amber-400', 'label' => 'در حال بررسی', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'rejected' => ['bg' => 'bg-rose-50 dark:bg-rose-900/20', 'text' => 'text-rose-600 dark:text-rose-400', 'label' => 'رد شده', 'icon' => 'M6 18L18 6M6 6l12 12'],
                                    ];
                                    $kyc = $kycStatuses[$vendor->kyc_status] ?? $kycStatuses['pending'];
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium {{ $kyc['bg'] }} {{ $kyc['text'] }}">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $kyc['icon'] }}"/></svg>
                                    {{ $kyc['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statuses = [
                                        'active' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-600 dark:text-emerald-400', 'label' => 'فعال'],
                                        'pending' => ['bg' => 'bg-gray-100 dark:bg-gray-800', 'text' => 'text-gray-600 dark:text-gray-400', 'label' => 'تعلیق / تایید نشده'],
                                        'suspended' => ['bg' => 'bg-rose-50 dark:bg-rose-900/20', 'text' => 'text-rose-600 dark:text-rose-400', 'label' => 'مسدود'],
                                    ];
                                    $status = $statuses[$vendor->status] ?? $statuses['pending'];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $status['bg'] }} {{ $status['text'] }}">
                                    {{ $status['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    @can('market.vendors.manage')
                                        @php
                                            $lockKey = 'vendor_edit_lock_' . $vendor->id;
                                            $lockedBy = \Illuminate\Support\Facades\Cache::get($lockKey);
                                            $isLocked = $lockedBy && $lockedBy !== auth()->id();
                                        @endphp

                                        @if($isLocked)
                                            <button type="button" class="p-1.5 rounded-lg text-gray-400 bg-gray-100 dark:bg-gray-700/50 cursor-not-allowed relative" title="در حال بررسی توسط همکاران">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                <span class="absolute top-1 right-1 w-2 h-2 bg-amber-500 rounded-full animate-ping"></span>
                                                <span class="absolute top-1 right-1 w-2 h-2 bg-amber-500 rounded-full"></span>
                                            </button>
                                        @else
                                            <a href="{{ route('user.market.vendors.edit', $vendor) }}"
                                               class="p-1.5 rounded-lg text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/20 relative" title="بررسی و ویرایش">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                @if($vendor->kyc_status === 'pending')
                                                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full animate-ping"></span>
                                                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                                                @endif
                                            </a>
                                        @endif

                                        <form action="{{ route('user.market.vendors.destroy', $vendor) }}" method="POST"
                                              onsubmit="return confirm('آیا از حذف این فروشگاه اطمینان دارید؟ تمام محصولات این فروشگاه نیز حذف خواهند شد.');"
                                              class="inline-block">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20" title="حذف">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <p class="text-base font-medium">فروشگاهی با این مشخصات یافت نشد.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($vendors->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20">
                    {{ $vendors->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
