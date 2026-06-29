@extends('layouts.user')

@section('content')
    <div class="space-y-6 max-w-6xl mx-auto">
        @php
            $user = auth()->user();
            $canCreateService =
                $user &&
                (
                    $user->can('booking.services.create')
                    || (($isProvider ?? false) && ($settings->allow_role_service_creation ?? false))
                );
            
            // Group services by category name for display (supports multiple categories per service)
            $groupedArray = [];
            foreach ($services->getCollection() as $service) {
                if ($service->categories && $service->categories->count() > 0) {
                    foreach ($service->categories as $cat) {
                        $groupedArray[$cat->name][] = $service;
                    }
                } elseif ($service->category) {
                    // Fallback to single category if no pivot is found
                    $groupedArray[$service->category->name][] = $service;
                } else {
                    $groupedArray['بدون دسته‌بندی'][] = $service;
                }
            }
            
            $groupedServices = collect($groupedArray)->map(function ($items) {
                return collect($items);
            });
        @endphp

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    سرویس‌های نوبت‌دهی
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">لیست سرویس‌ها، فیلتر و گروه‌بندی بر اساس دسته‌ها.</p>
            </div>
            @if($canCreateService)
                <a class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all active:scale-[0.98]"
                   href="{{ route('user.booking.services.create') }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    ایجاد سرویس جدید
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 rounded-xl border border-emerald-200 dark:border-emerald-500/20 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-800 dark:text-emerald-300 px-4 py-3 shadow-sm" role="alert">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter Bar -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm p-4">
            <form method="GET" action="{{ route('user.booking.services.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Search -->
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="جستجوی نام سرویس..."
                        class="w-full pl-3 pr-10 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-colors">
                </div>

                <!-- Category Filter -->
                <div>
                    <select name="category_id" class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-colors">
                        <option value="">همه دسته‌بندی‌ها</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <select name="status" class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-colors">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>فعال</option>
                        <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>غیرفعال</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 inline-flex justify-center items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 text-white text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                        فیلتر
                    </button>
                    @if(request()->hasAny(['search', 'category_id', 'status']))
                        <a href="{{ route('user.booking.services.index') }}" class="inline-flex items-center justify-center p-2.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500/20 transition-colors" title="حذف فیلترها">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Grouped Services List -->
        @forelse($groupedServices as $categoryName => $group)
            <div class="space-y-3">
                <div class="flex items-center gap-3 pl-2">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">{{ $categoryName }}</h3>
                    <div class="h-px bg-slate-200 dark:bg-slate-700/60 flex-1"></div>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-full">{{ $group->count() }} سرویس</span>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full whitespace-nowrap text-sm text-right">
                            <thead class="bg-slate-50/70 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700/60">
                                <tr>
                                    <th class="px-5 py-4 font-semibold text-slate-600 dark:text-slate-300 w-16">#</th>
                                    <th class="px-5 py-4 font-semibold text-slate-600 dark:text-slate-300">نام سرویس</th>
                                    <th class="px-5 py-4 font-semibold text-slate-600 dark:text-slate-300">وضعیت</th>
                                    <th class="px-5 py-4 font-semibold text-slate-600 dark:text-slate-300">قیمت پایه (تومان)</th>
                                    <th class="px-5 py-4 font-semibold text-slate-600 dark:text-slate-300">فرم اختصاصی</th>
                                    <th class="px-5 py-4 font-semibold text-slate-600 dark:text-slate-300 text-left">عملیات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                @foreach($group as $srv)
                                    @php
                                        $isPublic = is_null($srv->owner_user_id) || in_array((int)$srv->owner_user_id, $adminOwnerIds ?? [], true);
                                        $spRow = $srv->serviceProviders->first();
                                        $isActiveForMe = (bool)($spRow?->is_active ?? false);
                                        $canEditService = in_array($srv->id, $editableServiceIds ?? []);
                                        $showToggleForMe = (($isProvider ?? false) && $isPublic);
                                    @endphp
                                    <tr class="group/row hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors duration-200">
                                        <td class="px-5 py-4 text-slate-500 dark:text-slate-400 font-mono text-xs">
                                            {{ $srv->id }}
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-slate-900 dark:text-white">{{ $srv->name }}</span>
                                                    @if($srv->online_booking_mode === 'FORCE_OFF')
                                                        <span class="text-[11px] text-rose-500 dark:text-rose-400 mt-0.5">رزرو آنلاین غیرفعال</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            @if($srv->status === 'ACTIVE')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                    فعال
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                    غیرفعال
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 font-semibold text-slate-800 dark:text-slate-200">
                                            {{ number_format($srv->base_price) }}
                                        </td>
                                        <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                            @if($srv->appointmentForm)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 text-xs font-medium">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                    {{ $srv->appointmentForm->name }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 dark:text-slate-500">-</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-left">
                                            <div class="flex items-center justify-end gap-2">
                                                @if($canEditService)
                                                    <a href="{{ route('user.booking.services.edit', $srv) }}" class="p-2 rounded-xl text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400 transition-colors" title="ویرایش سرویس">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                    </a>
                                                    <a href="{{ route('user.booking.services.availability.edit', $srv) }}" class="p-2 rounded-xl text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-500/10 dark:hover:text-blue-400 transition-colors" title="برنامه زمانی">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                    </a>
                                                    <a href="{{ route('user.booking.services.custom-prices', $srv->id) }}" class="p-2 rounded-xl text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 dark:hover:text-amber-400 transition-colors" title="تنظیمات قیمت و برند">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    </a>
                                                @endif

                                                @if($showToggleForMe)
                                                    <div class="w-px h-6 bg-slate-200 dark:bg-slate-700 mx-1"></div>
                                                    <form method="POST" action="{{ route('user.booking.services.toggleForMe', $srv) }}" class="inline-flex items-center gap-2">
                                                        @csrf
                                                        <button type="submit" class="relative w-11 h-6 rounded-full transition-colors {{ $isActiveForMe ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600' }}" title="{{ $isActiveForMe ? 'غیرفعال برای من' : 'فعال‌سازی برای من' }}">
                                                            <div class="absolute top-1 {{ $isActiveForMe ? 'left-1' : 'right-1' }} w-4 h-4 rounded-full bg-white transition-transform"></div>
                                                        </button>
                                                        <span class="text-[11px] font-medium {{ $isActiveForMe ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500' }}">
                                                            {{ $isActiveForMe ? 'فعال من' : 'غیرفعال من' }}
                                                        </span>
                                                    </form>
                                                @endif

                                                @if(! $canEditService && ! $showToggleForMe)
                                                    <span class="text-xs font-medium text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 px-3 py-1.5 rounded-lg">فقط مشاهده</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-16 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm text-center px-4">
                <div class="w-20 h-20 bg-slate-50 dark:bg-slate-900 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-1">هیچ سرویسی یافت نشد</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto">با فیلترهای فعلی نتیجه‌ای پیدا نکردیم. لطفاً جستجوی خود را تغییر دهید یا یک سرویس جدید بسازید.</p>
                @if(request()->hasAny(['search', 'category_id', 'status']))
                    <a href="{{ route('user.booking.services.index') }}" class="mt-6 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 underline underline-offset-4">پاک کردن فیلترها</a>
                @endif
            </div>
        @endforelse

        <!-- Pagination -->
        @if($services->hasPages())
            <div class="flex justify-center mt-8">
                {{ $services->links() }}
            </div>
        @endif
    </div>
@endsection
