@extends('layouts.user')

@php
    $title = 'لیست '.config('clients.labels.plural');
        $labelPlural = config('clients.labels.plural', 'مشتریان');
        $user = auth()->user();

        $canViewAll      = $user?->hasRole('super-admin')
                            || $user?->can('clients.view.all')
                            || $user?->can('clients.manage');

        $canViewAssigned = $user?->can('clients.view.assigned');
        $canViewOwn      = $user?->can('clients.view.own');

        if ($canViewAll) {
            $visibilityLabel = 'شما در حال مشاهده همه ' . $labelPlural . ' سیستم هستید.';
        } elseif ($canViewAssigned) {
            $visibilityLabel = 'شما در حال مشاهده ' . $labelPlural . 'ی هستید که خودتان ایجاد کرده‌اید یا از طریق فیلدهای انتساب به شما واگذار شده‌اند.';
        } elseif ($canViewOwn) {
            $visibilityLabel = 'شما فقط ' . $labelPlural . 'ی را می‌بینید که خودتان ایجاد کرده‌اید.';
        } else {
            // حالت محافظه‌کارانه: اگر فقط clients.view (بدون .own/.assigned/.all) دارد
            $visibilityLabel = 'شما فقط ' . $labelPlural . 'ی را می‌بینید که خودتان ایجاد کرده‌اید.';
        }
        $clientCallsModule = \App\Models\Module::where('slug', 'clientcalls')->first();
        $followUpsModule   = \App\Models\Module::where('slug', 'followups')->first();
@endphp

@section('content')
    <div class="space-y-4" x-data="{ selectedIds: [], allChecked: false, bulkStatusId: '' }">
        {{-- هدر و ابزارها --}}
        <div
            class="flex flex-col-2 sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    {{ request('trashed') == '1' ? 'سطل زباله مشتریان' : config('clients.labels.plural', 'مشتریان') }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ request('trashed') == '1' ? 'مشاهده و مدیریت مشتریان حذف شده' : $visibilityLabel }}
                </p>
            </div>

            <div class="flex items-center gap-3 self-end sm:self-auto">
                @if(request('trashed') != '1')
                    {{-- دکمه ایجاد کامل --}}
                    @can('clients.create')
                        @if (Route::has('user.clients.create'))
                            <a href="{{ route('user.clients.create') }}"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ 'ایجاد ' . config('clients.labels.singular', 'مشتری') }}
                            </a>
                        @endif
                    @endcan

                    {{-- ویجت ایجاد سریع (Livewire) --}}
                    @can('clients.create')
                        @livewire('clients.form', ['asQuickWidget' => true], key('clients-quick-widget'))
                    @endcan
                @endif
            </div>
        </div>

        {{-- Tabs --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex gap-6" aria-label="Tabs">
                <a href="{{ route('user.clients.index') }}"
                   class="{{ !request('trashed') ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    همه {{ config('clients.labels.plural', 'مشتریان') }}
                </a>
                @can('clients.delete')
                <a href="{{ route('user.clients.index', ['trashed' => 1]) }}"
                   class="{{ request('trashed') == '1' ? 'border-red-500 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    سطل زباله
                </a>
                @endcan
            </nav>
        </div>

        {{-- پنل فیلتر پیشرفته --}}
        @if(request('trashed') != '1')
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                    فیلترهای پیشرفته
                </h2>
                @if(request()->except('page'))
                    <a href="{{ route('user.clients.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        حذف فیلترها
                    </a>
                @endif
            </div>
            <div class="p-5">
                <form action="{{ route('user.clients.index') }}" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5">
                        {{-- جستجوی متنی --}}
                        <div>
                            <label for="search" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">جستجو</label>
                            <div class="relative">
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                       placeholder="نام، ایمیل، تلفن، کد ملی و..."
                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </div>
                            </div>
                        </div>

                        {{-- فیلتر بر اساس ایجاد کننده --}}
                        <div>
                            <label for="created_by" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">ایجاد کننده</label>
                            <div class="relative">
                                <select name="created_by" id="created_by"
                                        class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                                    <option value="">همه</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected(request('created_by') == $user->id)>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </div>
                            </div>
                        </div>

                        {{-- فیلتر بر اساس وضعیت --}}
                        <div>
                            <label for="status_id" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">وضعیت</label>
                            <div class="relative">
                                <select name="status_id" id="status_id"
                                        class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                                    <option value="">همه</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" @selected(request('status_id') == $status->id)>
                                            {{ $status->label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </div>
                        </div>

                        {{-- ترتیب نمایش --}}
                        <div>
                            <label for="sort" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">ترتیب نمایش</label>
                            <div class="relative">
                                <select name="sort" id="sort"
                                        class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                                    <option value="newest" @selected(request('sort', 'newest') == 'newest')>جدیدترین</option>
                                    <option value="oldest" @selected(request('sort') == 'oldest')>قدیمی‌ترین</option>
                                    <option value="name_asc" @selected(request('sort') == 'name_asc')>نام (الف تا ی)</option>
                                    <option value="name_desc" @selected(request('sort') == 'name_desc')>نام (ی تا الف)</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M6 8h12m-8 4h4" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-1 sm:col-span-2 md:col-span-4 flex items-center justify-end gap-4 pt-4 border-t border-gray-100 dark:border-gray-700 mt-5">
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 dark:focus:ring-indigo-900 transition-all shadow-lg shadow-indigo-500/30 active:scale-95">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                            اعمال فیلتر
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- پنل عملیات گروهی --}}
        @if(request('trashed') != '1')
        <div x-show="selectedIds.length > 0"
             x-transition
             class="flex flex-wrap items-center justify-between gap-4 p-4 mb-6 bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800 rounded-2xl">
            <div class="flex items-center gap-2 text-sm text-indigo-800 dark:text-indigo-300">
                <span class="font-bold text-base" x-text="selectedIds.length"></span>
                <span>مشتری انتخاب شده است.</span>
            </div>
            
            <form method="POST" action="{{ route('user.clients.bulk-update') }}" class="flex flex-wrap items-center gap-3">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>

                <div class="flex items-center gap-2">
                    <select name="status_id" x-model="bulkStatusId" class="rounded-xl border border-indigo-300 dark:border-indigo-700 bg-white dark:bg-gray-900 px-3 py-1.5 text-xs text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        <option value="">تغییر وضعیت به...</option>
                        @foreach($statuses ?? [] as $status)
                            <option value="{{ $status->id }}">{{ $status->label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" name="action" value="status" :disabled="!bulkStatusId" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs font-medium transition-colors">
                        اعمال وضعیت
                    </button>
                </div>

                <div class="h-4 w-px bg-indigo-200 dark:bg-indigo-800 hidden sm:block"></div>

                <button type="submit" name="action" value="delete" onclick="return confirm('آیا از حذف گروهی مشتریان انتخاب شده مطمئن هستید؟')" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-medium transition-colors">
                    حذف گروهی
                </button>
            </form>
        </div>
        @endif

        {{-- جدول --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="w-12 px-4 py-3 text-right">
                            <input type="checkbox" x-model="allChecked" @change="selectedIds = allChecked ? [{{ implode(',', $clients->pluck('id')->toArray()) }}] : []" class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">#</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">اطلاعات کاربری</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">تماس</th>
                        @if($clientCallsModule && $clientCallsModule->installed && $clientCallsModule->active)
                            @can('client-calls.create')
                                <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">تماس‌ها</th>
                            @endcan
                        @endif
                        @if($followUpsModule && $followUpsModule->installed && $followUpsModule->active)
                            @can('followups.create')
                                <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">پیگیری‌ها</th>
                            @endcan
                        @endif
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">وضعیت</th> {{-- New Status Header --}}
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">ایجاد کننده</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($clients as $client)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-150">
                            <td class="w-12 px-4 py-3">
                                <input type="checkbox" :value="{{ $client->id }}" x-model="selectedIds" @change="allChecked = (selectedIds.length === {{ $clients->count() }})" class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </td>
                            {{-- ID --}}
                            <td class="px-4 py-3 text-gray-400 font-mono text-xs">
                                {{ $client->id }}
                            </td>

                            {{-- User Info --}}
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span
                                        class="font-medium text-gray-900 dark:text-white">{{ $client->full_name }}</span>
                                    <span
                                        class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">@ {{ $client->username }}</span>
                                </div>
                            </td>

                            {{-- Contact --}}
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1 text-xs">
                                    @if($client->email)
                                        <div
                                            class="flex items-center gap-1 text-gray-600 dark:text-gray-300 dir-ltr text-right">
                                            <span class="opacity-70">✉️</span> {{ $client->email }}
                                        </div>
                                    @endif
                                    @if($client->phone)
                                        <div
                                            class="flex items-center gap-1 text-gray-600 dark:text-gray-300 dir-ltr text-right">
                                            <span class="opacity-70">📞</span> {{ $client->phone }}
                                        </div>
                                    @endif
                                    @if(!$client->email && !$client->phone)
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Calls (popup) --}}
                            @if($clientCallsModule && $clientCallsModule->installed && $clientCallsModule->active)
                                @can('client-calls.create')
                                    <td class="px-4 py-3 align-top text-xs text-gray-600 dark:text-gray-300">
                                        @include('clientcalls::components.client-call-manager', ['client' => $client])
                                    </td>
                                @endcan
                            @endif

                            {{-- FollowUps (popup) --}}
                            @if($followUpsModule && $followUpsModule->installed && $followUpsModule->active)
                                @can('followups.create')
                                    <td class="px-4 py-3 align-top text-xs text-gray-600 dark:text-gray-300">
                                        @include('followups::components.client-followup-manager', ['client' => $client])
                                    </td>
                                @endcan
                            @endif

                            {{-- Status --}}
                            <td class="px-4 py-3">
                                @if($client->status)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          style="background-color: {{ $client->status->color }}20; color: {{ $client->status->color }};">
                                        {{ $client->status->label }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Creator --}}
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                @if(optional($client->creator)->name)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-700 text-xs">
                                        {{ $client->creator->name }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    @if(request('trashed') == '1')
                                        @can('clients.delete')
                                            {{-- Restore --}}
                                            <form action="{{ route('user.clients.restore', $client->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit"
                                                        class="p-1.5 rounded-lg text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/20"
                                                        title="بازیابی">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 6.571M21 12a9 9 0 11-18 0" />
                                                    </svg>
                                                </button>
                                            </form>
                                            {{-- Force Delete --}}
                                            <form action="{{ route('user.clients.force-delete', $client->id) }}" method="POST"
                                                  onsubmit="return confirm('آیا از حذف دائمی این مشتری مطمئن هستید؟ این عمل غیر قابل بازگشت است.');"
                                                  class="inline-block">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                                        title="حذف دائمی">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    @else
                                        @can('clients.view')
                                            <a href="{{ route('user.clients.show', $client) }}"
                                               class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20"
                                               title="مشاهده">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                        @endcan

                                        @can('clients.edit')
                                            <a href="{{ route('user.clients.edit', $client) }}"
                                               class="p-1.5 rounded-lg text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/20"
                                               title="ویرایش">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endcan

                                        @can('clients.delete')
                                            <form action="{{ route('user.clients.destroy', $client) }}" method="POST"
                                                  onsubmit="return confirm('آیا از حذف این مورد اطمینان دارید؟');"
                                                  class="inline-block">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                                        title="حذف">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center"> {{-- Updated colspan to 6 --}}
                                <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    <p class="text-base font-medium">هیچ مشتری‌ای یافت نشد</p>
                                    <p class="text-sm mt-1">می‌توانید یک مشتری جدید اضافه کنید.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- صفحه بندی --}}
            @if($clients->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
