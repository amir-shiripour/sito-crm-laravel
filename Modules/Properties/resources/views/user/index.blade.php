@extends('layouts.user')

@php
    $title = 'لیست املاک';
    // استایل‌های مشترک
    $badgeClass = "inline-flex items-center px-2 py-1 rounded-md text-xs font-medium";
    $canManageProperties = auth()->user()->can('properties.manage');
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </span>
                    لیست املاک
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">
                    مدیریت و مشاهده وضعیت املاک ثبت شده
                </p>
            </div>

            <div class="flex items-center gap-3 self-end sm:self-auto">
                <a href="{{ route('user.properties.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    افزودن ملک جدید
                </a>
            </div>
        </div>

        {{-- فیلترها --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    فیلترهای پیشرفته
                </h2>

                @if(request()->anyFilled(['search', 'listing_type', 'property_type', 'status_id', 'publication_status', 'agent_id', 'show_all']))
                    <a href="{{ route('user.properties.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        حذف فیلترها
                    </a>
                @endif
            </div>

            <div class="p-5">
                <form action="{{ route('user.properties.index') }}" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

                        {{-- جستجو --}}
                        <div class="col-span-1 sm:col-span-2 lg:col-span-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">جستجو</label>
                            <div class="relative">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="عنوان، کد، آدرس..."
                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- نوع معامله --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">نوع معامله</label>
                            <div class="relative">
                                <select name="listing_type" class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                                    <option value="">همه موارد</option>
                                    <option value="sale" {{ request('listing_type') == 'sale' ? 'selected' : '' }}>فروش</option>
                                    <option value="rent" {{ request('listing_type') == 'rent' ? 'selected' : '' }}>رهن و اجاره</option>
                                    <option value="presale" {{ request('listing_type') == 'presale' ? 'selected' : '' }}>پیش‌فروش</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- نوع ملک --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">نوع ملک</label>
                            <div class="relative">
                                <select name="property_type" class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                                    <option value="">همه موارد</option>
                                    <option value="apartment" {{ request('property_type') == 'apartment' ? 'selected' : '' }}>آپارتمان</option>
                                    <option value="villa" {{ request('property_type') == 'villa' ? 'selected' : '' }}>ویلا</option>
                                    <option value="land" {{ request('property_type') == 'land' ? 'selected' : '' }}>زمین</option>
                                    <option value="office" {{ request('property_type') == 'office' ? 'selected' : '' }}>اداری</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- وضعیت --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">وضعیت</label>
                            <div class="relative">
                                <select name="status_id" class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                                    <option value="">همه وضعیت‌ها</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>{{ $status->label ?? $status->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- وضعیت انتشار --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">وضعیت انتشار</label>
                            <div class="relative">
                                <select name="publication_status" class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                                    <option value="">همه</option>
                                    <option value="published" {{ request('publication_status') == 'published' ? 'selected' : '' }}>منتشر شده</option>
                                    <option value="draft" {{ request('publication_status') == 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- مشاور (فقط برای مدیران) --}}
                        @if($canManageProperties && !empty($agents))
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">مشاور</label>
                                <div class="relative">
                                    <select name="agent_id" class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                                        <option value="">همه مشاوران</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- دکمه‌ها و چک‌باکس --}}
                        <div class="col-span-1 sm:col-span-2 lg:col-span-4 flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-100 dark:border-gray-700 mt-2">

                            {{-- نمایش همه (فقط برای مدیران) --}}
                            @if(auth()->user()->hasRole('super-admin') || auth()->user()->can('properties.view.all') || auth()->user()->can('properties.manage'))
                                <label class="inline-flex items-center cursor-pointer group">
                                    <div class="relative">
                                        <input type="checkbox" name="show_all" value="1" class="sr-only peer" {{ request('show_all') ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                    </div>
                                    <span class="mr-3 text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 transition-colors">نمایش همه املاک</span>
                                </label>
                            @else
                                <div></div> {{-- Spacer --}}
                            @endif

                            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 dark:focus:ring-indigo-900 transition-all shadow-lg shadow-indigo-500/30 active:scale-95">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                اعمال فیلتر
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- جدول --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">ملک</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">قیمت</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">نوع</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">وضعیت</th>
                        @if($canManageProperties)
                            <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">مشاور</th>
                            <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">ایجاد کننده</th>
                        @endif
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($properties as $property)
                        @php
                            $isMyProperty = ($property->created_by === auth()->id() || $property->agent_id === auth()->id());
                            $rowClass = $isMyProperty
                                ? 'bg-indigo-50/60 dark:bg-indigo-900/10 hover:bg-indigo-100 dark:hover:bg-indigo-900/20'
                                : 'hover:bg-gray-50 dark:hover:bg-gray-700/20';

                            // Check Permissions
                            $canEdit = auth()->user()->hasRole('super-admin') ||
                                       auth()->user()->can('properties.edit.all') ||
                                       (auth()->user()->can('properties.edit') && ($isMyProperty || auth()->user()->can('properties.manage')));

                            $canDelete = auth()->user()->hasRole('super-admin') ||
                                         auth()->user()->can('properties.delete.all') ||
                                         (auth()->user()->can('properties.delete') && ($isMyProperty || auth()->user()->can('properties.manage')));
                        @endphp
                        <tr class="group {{ $rowClass }} transition-colors duration-150">

                            {{-- عنوان و تصویر --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0 border border-gray-200 dark:border-gray-600">
                                        @if($property->cover_image)
                                            <img src="{{ asset('storage/' . $property->cover_image) }}" class="w-full h-full object-cover" alt="{{ $property->title }}">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900 dark:text-white line-clamp-1 max-w-[200px]" title="{{ $property->title }}">{{ $property->title }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">Code: {{ $property->code }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- قیمت --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    @if($property->listing_type == 'sale' || $property->listing_type == 'presale')
                                        <span class="text-gray-900 dark:text-gray-100 font-medium">
                                            {{ $property->price > 0 ? number_format($property->price) . ' تومان' : 'توافقی' }}
                                        </span>
                                        @if($property->min_price > 0)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                (کف: {{ number_format($property->min_price) }} تومان)
                                            </span>
                                        @endif
                                    @elseif($property->listing_type == 'rent')
                                        <div class="text-xs text-gray-500">
                                            <div>رهن: <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $property->deposit_price > 0 ? number_format($property->deposit_price) : 'توافقی' }}</span></div>
                                            <div>اجاره: <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $property->rent_price > 0 ? number_format($property->rent_price) : 'توافقی' }}</span></div>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- نوع معامله --}}
                            <td class="px-6 py-4">
                                @php
                                    $typeClass = match($property->listing_type) {
                                        'sale' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-800',
                                        'rent' => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
                                        'presale' => 'bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800',
                                        default => 'bg-gray-50 text-gray-700 border-gray-100'
                                    };
                                    $typeLabel = match($property->listing_type) {
                                        'sale' => 'فروش',
                                        'rent' => 'رهن و اجاره',
                                        'presale' => 'پیش‌فروش',
                                        default => $property->listing_type
                                    };
                                @endphp
                                <span class="{{ $badgeClass }} border {{ $typeClass }}">
                                    {{ $typeLabel }}
                                </span>
                            </td>

                            {{-- وضعیت --}}
                            <td class="px-6 py-4">
                                @if($property->status)
                                    <span class="{{ $badgeClass }}"
                                          style="background-color: {{ $property->status->color }}15; color: {{ $property->status->color }}; border: 1px solid {{ $property->status->color }}30;">
                                        {{ $property->status->label ?? $property->status->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- مشاور (Agent) - نمایش شرطی --}}
                            @if($canManageProperties)
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900/20 flex items-center justify-center text-[10px] text-purple-600 dark:text-purple-300">
                                            {{ mb_substr(optional($property->agent)->name ?? '?', 0, 1) }}
                                        </div>
                                        <span class="text-xs">{{ optional($property->agent)->name ?? 'نامشخص' }}</span>
                                    </div>
                                </td>
                            @endif

                            {{-- ایجاد کننده (Creator) - نمایش شرطی --}}
                            @if($canManageProperties)
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-[10px] text-gray-500">
                                            {{ mb_substr(optional($property->creator)->name ?? '?', 0, 1) }}
                                        </div>
                                        <span class="text-xs">{{ optional($property->creator)->name ?? 'نامشخص' }}</span>
                                    </div>
                                </td>
                            @endif

                            {{-- عملیات --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('properties.show', $property->slug) }}" target="_blank"
                                       class="p-2 rounded-lg text-emerald-600 bg-emerald-50 hover:bg-emerald-100 dark:text-emerald-400 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 transition-colors"
                                       title="مشاهده در سایت">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    @if($canEdit)
                                        <a href="{{ route('user.properties.edit', $property) }}"
                                           class="p-2 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:text-indigo-400 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/40 transition-colors"
                                           title="ویرایش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    @endif

                                    @if($canDelete)
                                        <form action="{{ route('user.properties.destroy', $property) }}" method="POST"
                                              onsubmit="return confirm('آیا از حذف این ملک اطمینان دارید؟');"
                                              class="inline-block">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 rounded-lg text-red-600 bg-red-50 hover:bg-red-100 dark:text-red-400 dark:bg-red-900/20 dark:hover:bg-red-900/40 transition-colors"
                                                    title="حذف">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManageProperties ? '7' : '5' }}" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                    <svg class="w-16 h-16 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    <p class="text-base font-medium text-gray-900 dark:text-white">هیچ ملکی یافت نشد</p>
                                    <p class="text-sm mt-1">اولین ملک خود را ثبت کنید.</p>
                                    <a href="{{ route('user.properties.create') }}" class="mt-4 text-indigo-600 hover:underline text-sm font-bold">افزودن ملک جدید</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($properties->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20">
                    {{ $properties->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
