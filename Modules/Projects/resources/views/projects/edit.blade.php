@php use App\Models\User;use Morilog\Jalali\Jalalian; @endphp
@extends('layouts.user')
@section('title', 'ویرایش پروژه «' . $project->title . '»')

@php
    $initialCustomerData = (!empty($initialClient)) ? [
        'id'    => $initialClient->id,
        'name'  => $initialClient->full_name,
        'label' => $initialClient->full_name . ($initialClient->phone ? " ({$initialClient->phone})" : ''),
        'phone' => $initialClient->phone ?? '',
        'email' => $initialClient->email ?? '',
    ] : null;

    $initialStartDate = old('start_date', ($project->start_date && class_exists(Jalalian::class)) ? Jalalian::fromCarbon($project->start_date)->format('Y/m/d') : ($project->start_date ? $project->start_date->format('Y/m/d') : ''));
    $initialEndDate = old('end_date', ($project->end_date && class_exists(Jalalian::class)) ? Jalalian::fromCarbon($project->end_date)->format('Y/m/d') : ($project->end_date ? $project->end_date->format('Y/m/d') : ''));

    $oldMembers = old('members');
    if ($oldMembers && is_array($oldMembers)) {
        $memberUserIds = collect($oldMembers)->pluck('user_id')->filter()->toArray();
        $loadedUsers = !empty($memberUserIds) ? User::whereIn('id', $memberUserIds)->get()->keyBy('id') : collect();
        $initialMembers = collect($oldMembers)->map(function ($m) use ($loadedUsers) {
            $u = $loadedUsers->get($m['user_id'] ?? null);
            return [
                'user_id'    => (string)($m['user_id'] ?? ''),
                'user_name'  => $u?->name ?? '',
                'user_email' => $u?->email ?? ($u?->mobile ?? ''),
                'role'       => $m['role'] ?? 'viewer',
            ];
        })->values()->toArray();
    } else {
        $initialMembers = $project->members->map(function ($m) {
            return [
                'user_id'    => (string)$m->user_id,
                'user_name'  => $m->user?->name ?? '',
                'user_email' => $m->user?->email ?? ($m->user?->mobile ?? ''),
                'role'       => $m->role ?? 'viewer',
            ];
        })->values()->toArray();
    }
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6"
         x-data="projectForm({
             initialCustomer: {{ json_encode($initialCustomerData) }},
             initialMembers: {{ json_encode($initialMembers) }},
             startDate: '{{ $initialStartDate }}',
             endDate: '{{ $initialEndDate }}'
         })">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <span
                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-amber-500 text-white shadow-lg shadow-amber-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </span>
                ویرایش پروژه «{{ $project->title }}»
            </h1>
            <a href="{{ route('projects.projects.show', $project) }}"
               class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-bold hover:bg-gray-200 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                مشاهده پروژه
            </a>
        </div>

        {{-- Errors --}}
        @if($errors->any())
            <div
                class="rounded-2xl bg-red-50 p-4 border border-red-100 dark:bg-red-900/10 dark:border-red-800/30 text-red-700 dark:text-red-400 text-sm font-medium">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Edit Form --}}
        <form method="POST" action="{{ route('projects.projects.update', $project) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div
                class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6 sm:p-8 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Title --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                            نام پروژه <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title', $project->title) }}" required
                               placeholder="مثال: طراحی وب‌سایت شرکتی"
                               class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white">
                    </div>

                    {{-- Client --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                            مشتری مربوطه
                            <span class="text-gray-400 font-normal">(اختیاری)</span>
                        </label>

                        <input type="hidden" name="client_id" :value="selectedCustomer">

                        {{-- Search Input (shown when no customer is selected) --}}
                        <div x-show="!selectedCustomer" class="relative" @click.outside="customerDropdownOpen = false">
                            <div class="relative">
                                <input type="text"
                                       x-model="customerQuery"
                                       @focus="if(customerQuery.trim()) customerDropdownOpen = true"
                                       @input="customerDropdownOpen = true; searchClientsDebounced()"
                                       placeholder="جستجوی نام، موبایل یا مشخصات مشتری..."
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 pr-11 pl-11 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white"
                                       autocomplete="off">
                                <div
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                                    </svg>
                                </div>
                                <div x-show="customerSearching"
                                     class="absolute inset-y-0 left-0 pl-3.5 flex items-center">
                                    <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>

                            {{-- Dropdown list --}}
                            <div x-show="customerDropdownOpen && customerQuery.trim().length > 0" x-transition
                                 class="absolute right-0 left-0 top-full mt-1.5 z-50 max-h-60 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="c in searchResults" :key="c.id">
                                    <button type="button" @click="selectCustomer(c)"
                                            class="w-full text-right px-4 py-2.5 text-xs hover:bg-indigo-50 dark:hover:bg-indigo-900/20 flex items-center justify-between gap-3 transition-colors">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                        <span
                                            class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400 font-bold text-xs shrink-0"
                                            x-text="(c.name || '؟').trim().charAt(0)"></span>
                                            <div class="truncate text-right">
                                                <span class="font-bold text-gray-900 dark:text-white block truncate"
                                                      x-text="c.name"></span>
                                                <span
                                                    class="text-[11px] text-gray-400 dark:text-gray-400 block truncate"
                                                    x-text="c.label && c.label !== c.name ? c.label : [c.phone, c.email].filter(Boolean).join(' • ')"></span>
                                            </div>
                                        </div>
                                        <span
                                            class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded shrink-0">انتخاب</span>
                                    </button>
                                </template>

                                <div x-show="!customerSearching && searchResults.length === 0"
                                     class="p-3 text-center text-xs text-gray-400">
                                    مشتری‌ای با این مشخصات یافت نشد.
                                </div>
                            </div>
                        </div>

                        {{-- Selected Customer Badge --}}
                        <div x-show="selectedCustomer" x-transition>
                            <div
                                class="flex items-center justify-between p-2.5 rounded-xl border border-indigo-200 dark:border-indigo-800/60 bg-indigo-50/60 dark:bg-indigo-950/30">
                                <div class="flex items-center gap-2.5 min-w-0">
                                <span
                                    class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-600 text-white font-bold text-xs shrink-0"
                                    x-text="(selectedCustomerData?.name || '؟').trim().charAt(0)"></span>
                                    <div class="min-w-0">
                                        <p class="font-bold text-xs text-gray-900 dark:text-white truncate"
                                           x-text="selectedCustomerData?.name"></p>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate"
                                           x-text="selectedCustomerData?.label && selectedCustomerData?.label !== selectedCustomerData?.name ? selectedCustomerData?.label : [selectedCustomerData?.phone, selectedCustomerData?.email].filter(Boolean).join(' • ')"></p>
                                    </div>
                                </div>
                                <button type="button" @click="clearCustomer()"
                                        class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white dark:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-red-50 hover:text-red-600 hover:border-red-200 dark:hover:bg-red-900/30 dark:hover:text-red-400 transition-colors"
                                        title="تغییر مشتری">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span>تغییر</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                            دسته‌بندی <span class="text-red-500">*</span>
                        </label>
                        <select name="category_id" required
                                class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                            <option value="">انتخاب دسته‌بندی</option>
                            @foreach($categories as $category)
                                <option
                                    value="{{ $category->id }}" @selected(old('category_id', $project->category_id) == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                            وضعیت پروژه
                        </label>
                        <select name="status_id"
                                class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                            @foreach($statuses as $status)
                                <option
                                    value="{{ $status->id }}" @selected(old('status_id', $project->status_id) == $status->id)>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Code --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                            کد پروژه
                        </label>
                        <input type="text" name="code" value="{{ old('code', $project->code) }}"
                               class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white font-mono">
                    </div>

                    {{-- Dates (Jalali Datepicker with shortcuts and validity checks) --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                            تاریخ شروع
                        </label>
                        <div class="relative">
                            <input type="text"
                                   name="start_date"
                                   x-model="startDate"
                                   @change="startDate = $event.target.value; checkDateValidity('start')"
                                   @input="startDate = $event.target.value; checkDateValidity('start')"
                                   :data-jdp-max-date="endDate || ''"
                                   data-jdp
                                   data-jdp-only-date
                                   class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 pe-11 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white cursor-pointer"
                                   placeholder="انتخاب تاریخ شروع"
                                   autocomplete="off"
                                   readonly>
                            <svg
                                class="w-5 h-5 absolute end-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                            تاریخ پایان / سررسید
                        </label>
                        <div class="relative">
                            <input type="text"
                                   name="end_date"
                                   x-model="endDate"
                                   @change="endDate = $event.target.value; checkDateValidity('end')"
                                   @input="endDate = $event.target.value; checkDateValidity('end')"
                                   :data-jdp-min-date="startDate || ''"
                                   data-jdp
                                   data-jdp-only-date
                                   class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 pe-11 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white cursor-pointer"
                                   placeholder="انتخاب تاریخ سررسید"
                                   autocomplete="off"
                                   readonly>
                            <svg
                                class="w-5 h-5 absolute end-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex items-center gap-2 mt-3" x-show="startDate" x-transition>
                            <button type="button" @click="setEndDate('week')"
                                    class="flex-1 py-1.5 text-[11px] font-bold rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white border border-indigo-100 transition-all shadow-sm dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20 dark:hover:bg-indigo-500 dark:hover:text-white active:scale-95">
                                ۱ هفته بعد
                            </button>
                            <button type="button" @click="setEndDate('month')"
                                    class="flex-1 py-1.5 text-[11px] font-bold rounded-xl bg-violet-50 text-violet-600 hover:bg-violet-600 hover:text-white border border-violet-100 transition-all shadow-sm dark:bg-violet-500/10 dark:text-violet-400 dark:border-violet-500/20 dark:hover:bg-violet-500 dark:hover:text-white active:scale-95">
                                ۱ ماه بعد
                            </button>
                            <button type="button" @click="setEndDate('year')"
                                    class="flex-1 py-1.5 text-[11px] font-bold rounded-xl bg-fuchsia-50 text-fuchsia-600 hover:bg-fuchsia-600 hover:text-white border border-fuchsia-100 transition-all shadow-sm dark:bg-fuchsia-500/10 dark:text-fuchsia-400 dark:border-fuchsia-500/20 dark:hover:bg-fuchsia-500 dark:hover:text-white active:scale-95">
                                ۱ سال بعد
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Team Members Section --}}
                <div class="border-t border-gray-100 dark:border-gray-700/60 pt-6 space-y-4">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                اعضای تیم و نقش‌های پروژه
                            </h3>
                            <p class="text-xs text-gray-400 mt-0.5">کاربرانی که در این پروژه همکاری دارند و سطح دسترسی
                                هر کدام</p>
                        </div>
                        <button type="button" @click="addMember()"
                                class="px-3.5 py-2 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-all flex items-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            افزودن عضو
                        </button>
                    </div>

                    {{-- Table Header for Desktop --}}
                    <div class="hidden sm:flex items-center gap-4 px-3 text-[11px] font-bold text-gray-400">
                        <div class="flex-1">کاربر / عضو تیم</div>
                        <div class="w-48">نقش دسترسی</div>
                        <div class="w-10 text-center">عملیات</div>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(member, index) in members" :key="member._key || index">
                            <div
                                class="p-3.5 sm:p-4 bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-100 dark:border-gray-700/60 space-y-3 sm:space-y-0 sm:flex sm:items-center sm:gap-4 transition-all">

                                {{-- Hidden input for form submission --}}
                                <input type="hidden" :name="`members[${index}][user_id]`" :value="member.user_id">

                                {{-- User Selection (Search or Selected Card) --}}
                                <div class="flex-1 min-w-0">
                                    {{-- State 1: User Selected --}}
                                    <template x-if="member.user_id">
                                        <div
                                            class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div
                                                    class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0 border border-indigo-100 dark:border-indigo-800/50"
                                                    x-text="(member.user_name || '؟').trim().charAt(0)">
                                                </div>
                                                <div class="min-w-0 text-right">
                                                    <div
                                                        class="font-bold text-xs text-gray-900 dark:text-white truncate"
                                                        x-text="member.user_name"></div>
                                                    <div class="text-[11px] text-gray-400 dark:text-gray-400 truncate"
                                                         x-text="member.user_email || 'کاربر سیستم'"></div>
                                                </div>
                                            </div>
                                            <button type="button" @click="clearMemberUser(index)"
                                                    class="shrink-0 px-2.5 py-1 text-xs font-bold text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 bg-gray-100 dark:bg-gray-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors">
                                                تغییر
                                            </button>
                                        </div>
                                    </template>

                                    {{-- State 2: Search Input --}}
                                    <template x-if="!member.user_id">
                                        <div class="relative" @click.outside="member.dropdownOpen = false">
                                            <div class="relative">
                                                <input type="text"
                                                       x-model="member.searchQuery"
                                                       @focus="if(member.searchQuery && member.searchQuery.trim()) member.dropdownOpen = true"
                                                       @input="member.dropdownOpen = true; searchUsersDebounced(index)"
                                                       placeholder="جستجوی نام، ایمیل یا شماره همراه کاربر..."
                                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 pr-10 pl-10 py-2.5 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm"
                                                       autocomplete="off">
                                                <div
                                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                                                    </svg>
                                                </div>
                                                <div x-show="member.searching"
                                                     class="absolute inset-y-0 left-0 pl-3.5 flex items-center">
                                                    <svg class="animate-spin h-4 w-4 text-indigo-600"
                                                         xmlns="http://www.w3.org/2000/svg" fill="none"
                                                         viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                                                stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                </div>
                                            </div>

                                            {{-- Dropdown results --}}
                                            <div
                                                x-show="member.dropdownOpen && member.searchQuery && member.searchQuery.trim().length > 0"
                                                x-transition
                                                class="absolute right-0 left-0 top-full mt-1.5 z-[100] max-h-56 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl divide-y divide-gray-100 dark:divide-gray-700">
                                                <template x-for="u in member.searchResults" :key="u.id">
                                                    <button type="button" @click="selectMemberUser(index, u)"
                                                            class="w-full text-right px-4 py-2.5 text-xs hover:bg-indigo-50 dark:hover:bg-indigo-900/20 flex items-center justify-between gap-3 transition-colors">
                                                        <div class="flex items-center gap-2.5 min-w-0">
                                                            <div
                                                                class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0"
                                                                x-text="(u.name || '؟').trim().charAt(0)">
                                                            </div>
                                                            <div class="min-w-0 text-right">
                                                                <div
                                                                    class="font-bold text-gray-900 dark:text-white truncate"
                                                                    x-text="u.name"></div>
                                                                <div class="text-[10px] text-gray-400 truncate"
                                                                     x-text="u.label && u.label !== u.name ? u.label : [u.email, u.mobile].filter(Boolean).join(' • ')"></div>
                                                            </div>
                                                        </div>
                                                        <span
                                                            class="shrink-0 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded">
                                                        انتخاب
                                                    </span>
                                                    </button>
                                                </template>
                                                <div
                                                    x-show="!member.searching && (!member.searchResults || member.searchResults.length === 0)"
                                                    class="p-3 text-center text-xs text-gray-400">
                                                    کاربری با این مشخصات یافت نشد.
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                {{-- Role Selection --}}
                                <div class="w-full sm:w-48 shrink-0">
                                    <label class="block sm:hidden text-[11px] font-bold text-gray-500 mb-1">نقش
                                        دسترسی:</label>
                                    <select :name="`members[${index}][role]`" x-model="member.role"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-xs font-bold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all cursor-pointer shadow-sm">
                                        @if(isset($roles) && $roles->isNotEmpty())
                                            @foreach($roles as $r)
                                                <option value="{{ $r->name }}">{{ $r->display_name }} ({{ ucfirst($r->name) }})</option>
                                            @endforeach
                                        @else
                                            <option value="viewer">مشاهده‌گر (Viewer)</option>
                                            <option value="editor">ویرایشگر (Editor)</option>
                                            <option value="manager">مدیر پروژه (Manager)</option>
                                        @endif
                                    </select>
                                </div>

                                {{-- Remove Button --}}
                                <div class="flex justify-end sm:block shrink-0">
                                    <button type="button" @click="removeMember(index)"
                                            class="p-2.5 rounded-xl text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 border border-transparent hover:border-red-200 dark:hover:border-red-800/40 transition-all flex items-center gap-1.5"
                                            title="حذف عضو">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span class="sm:hidden text-xs font-bold text-red-600">حذف</span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        {{-- Empty State --}}
                        <div x-show="members.length === 0"
                             class="p-6 text-center rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20 space-y-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400">هنوز عضوی برای این پروژه اضافه نشده
                                است.</p>
                            <button type="button" @click="addMember()"
                                    class="text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                + افزودن اولین عضو
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <div class="border-t border-gray-100 dark:border-gray-700/60 pt-6">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                        توضیحات و اهداف پروژه
                    </label>
                    <textarea name="description" rows="4" placeholder="توضیحات کلی، اهداف و نکات مربوط به پروژه..."
                              class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white">{{ old('description', $project->description) }}</textarea>
                </div>
            </div>

            {{-- Sticky Bottom Submit Bar --}}
            <div class="sticky bottom-4 z-40">
                <div
                    class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl p-4 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-lg flex flex-row-reverse items-center justify-between gap-4">
                    <button type="submit"
                            class="flex-1 md:flex-none px-8 py-3.5 rounded-xl bg-linear-to-r from-amber-500 to-amber-600 text-white font-black shadow-lg shadow-amber-500/30 hover:shadow-amber-500/50 transition-all duration-300 active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        ذخیره تغییرات پروژه
                    </button>
                    <a href="{{ route('projects.projects.show', $project) }}"
                       class="px-6 py-3.5 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-xl dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        انصراف
                    </a>
                </div>
            </div>
        </form>

    </div>

    @push('scripts')
        <script>
            function projectForm(config = {}) {
                return {
                    members: (config.initialMembers || []).map((m, idx) => ({
                        _key: Date.now() + '_' + idx,
                        user_id: m.user_id ? String(m.user_id) : '',
                        user_name: m.user_name || '',
                        user_email: m.user_email || '',
                        role: m.role || 'viewer',
                        searchQuery: '',
                        dropdownOpen: false,
                        searching: false,
                        searchResults: [],
                        searchTimeout: null,
                    })),

                    searchUsersDebounced(index) {
                        const member = this.members[index];
                        if (!member) return;
                        clearTimeout(member.searchTimeout);
                        const q = (member.searchQuery || '').trim();
                        if (q.length < 1) {
                            member.searchResults = [];
                            member.searching = false;
                            return;
                        }

                        member.searching = true;
                        member.searchTimeout = setTimeout(async () => {
                            try {
                                const res = await fetch(`{{ route('projects.projects.users.search') }}?q=${encodeURIComponent(q)}`, {
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });
                                if (res.ok) {
                                    const data = await res.json();
                                    const items = Array.isArray(data) ? data : (data.results || data.data || []);
                                    member.searchResults = items.map(u => ({
                                        id: u.id,
                                        name: u.name || '',
                                        email: u.email || '',
                                        mobile: u.mobile || '',
                                        label: u.label || u.name || '',
                                    }));
                                }
                            } catch (e) {
                                console.error('User search error:', e);
                                member.searchResults = [];
                            } finally {
                                member.searching = false;
                            }
                        }, 300);
                    },

                    selectMemberUser(index, u) {
                        if (this.members.some((m, idx) => idx !== index && String(m.user_id) === String(u.id))) {
                            alert('این کاربر قبلاً به لیست اعضای پروژه اضافه شده است.');
                            return;
                        }
                        const member = this.members[index];
                        if (member) {
                            member.user_id = String(u.id);
                            member.user_name = u.name;
                            member.user_email = u.email || u.mobile || '';
                            member.dropdownOpen = false;
                            member.searchQuery = '';
                            member.searchResults = [];
                        }
                    },

                    clearMemberUser(index) {
                        const member = this.members[index];
                        if (member) {
                            member.user_id = '';
                            member.user_name = '';
                            member.user_email = '';
                            member.searchQuery = '';
                            member.searchResults = [];
                            member.dropdownOpen = false;
                        }
                    },

                    addMember() {
                        this.members.push({
                            _key: Date.now() + '_' + Math.random().toString(36).substr(2, 5),
                            user_id: '',
                            user_name: '',
                            user_email: '',
                            role: 'viewer',
                            searchQuery: '',
                            dropdownOpen: false,
                            searching: false,
                            searchResults: [],
                            searchTimeout: null,
                        });
                    },

                    removeMember(index) {
                        this.members.splice(index, 1);
                    },

                    selectedCustomer: config.initialCustomer ? String(config.initialCustomer.id) : '',
                    selectedCustomerData: config.initialCustomer || null,
                    customerQuery: '',
                    customerDropdownOpen: false,
                    customerSearching: false,
                    searchResults: [],
                    searchTimeout: null,

                    searchClientsDebounced() {
                        clearTimeout(this.searchTimeout);
                        const q = this.customerQuery.trim();
                        if (q.length < 1) {
                            this.searchResults = [];
                            this.customerSearching = false;
                            return;
                        }

                        this.customerSearching = true;
                        this.searchTimeout = setTimeout(async () => {
                            try {
                                const res = await fetch(`{{ route('user.clients.search') }}?q=${encodeURIComponent(q)}`, {
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });
                                if (res.ok) {
                                    const data = await res.json();
                                    const items = Array.isArray(data) ? data : (data.results || data.data || []);
                                    this.searchResults = items.map(c => ({
                                        id: c.id,
                                        name: c.full_name || c.name || '',
                                        label: c.label || c.full_name || c.name || '',
                                        phone: c.phone || '',
                                        email: c.email || '',
                                    }));
                                }
                            } catch (e) {
                                console.error('Client search error:', e);
                                this.searchResults = [];
                            } finally {
                                this.customerSearching = false;
                            }
                        }, 300);
                    },

                    selectCustomer(c) {
                        this.selectedCustomer = String(c.id);
                        this.selectedCustomerData = c;
                        this.customerDropdownOpen = false;
                        this.customerQuery = '';
                        this.searchResults = [];
                    },

                    clearCustomer() {
                        this.selectedCustomer = '';
                        this.selectedCustomerData = null;
                        this.customerQuery = '';
                        this.searchResults = [];
                        this.customerDropdownOpen = false;
                    },

                    startDate: config.startDate || '',
                    endDate: config.endDate || '',

                    init() {
                        this.$watch('startDate', (val) => {
                            if (val && this.endDate) {
                                this.checkDateValidity('start');
                            }
                        });
                        this.$watch('endDate', (val) => {
                            if (val && this.startDate) {
                                this.checkDateValidity('end');
                            }
                        });
                    },

                    checkDateValidity(trigger = 'start') {
                        if (this.startDate && this.endDate) {
                            let s = this.toEnglishNum(this.startDate).replace(/[^0-9]/g, '');
                            let e = this.toEnglishNum(this.endDate).replace(/[^0-9]/g, '');
                            if (s && e && s.length >= 8 && e.length >= 8 && s > e) {
                                if (trigger === 'start') {
                                    alert('تاریخ شروع پروژه نمی‌تواند دیرتر از تاریخ سررسید باشد!');
                                    this.startDate = '';
                                    let el = document.querySelector('input[name="start_date"]');
                                    if (el) {
                                        el.value = '';
                                    }
                                } else {
                                    alert('تاریخ سررسید پروژه نمی‌تواند زودتر از تاریخ شروع باشد!');
                                    this.endDate = '';
                                    let el = document.querySelector('input[name="end_date"]');
                                    if (el) {
                                        el.value = '';
                                    }
                                }
                            }
                        }
                    },

                    setEndDate(t) {
                        if (!this.startDate) {
                            alert('ابتدا تاریخ شروع را انتخاب کنید.');
                            return;
                        }
                        this.endDate = this.addJalali(this.startDate, 1, t);
                        this.checkDateValidity('end');
                        this.$nextTick(() => {
                            let el = document.querySelector('input[name="end_date"]');
                            if (el) {
                                el.value = this.endDate;
                                el.dispatchEvent(new Event('input', {bubbles: true}));
                                el.dispatchEvent(new Event('change', {bubbles: true}));
                            }
                        });
                    },

                    addJalali(j, a, t) {
                        if (!j) return '';
                        let e = this.toEnglishNum(j);
                        let p = e.split('/').map(Number);
                        if (p.length !== 3) return '';
                        let [y, m, d] = p;
                        if (t === 'year') {
                            y += a;
                        } else if (t === 'month') {
                            m += a;
                            while (m > 12) {
                                m -= 12;
                                y++;
                            }
                        } else if (t === 'week') {
                            d += 7 * a;
                            while (true) {
                                let mD = (m <= 6) ? 31 : (m <= 11 ? 30 : 29);
                                if (d <= mD) break;
                                d -= mD;
                                m++;
                                if (m > 12) {
                                    m = 1;
                                    y++;
                                }
                            }
                        }
                        let mD = (m <= 6) ? 31 : (m <= 11 ? 30 : 29);
                        if (d > mD) d = mD;
                        return `${y}/${m.toString().padStart(2, '0')}/${d.toString().padStart(2, '0')}`;
                    },

                    toEnglishNum(v) {
                        if (v === '' || v === null || v === undefined) return '';
                        const p = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                        const a = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                        return v.toString().replace(/[۰-۹]/g, d => p.indexOf(d)).replace(/[٠-٩]/g, d => a.indexOf(d));
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                if (window.jalaliDatepicker) {
                    window.jalaliDatepicker.startWatch();
                }
            });
        </script>
    @endpush

    @includeIf('partials.jalali-date-picker')
@endsection
