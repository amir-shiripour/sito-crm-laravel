@extends('layouts.user')

@php
    $title = 'لیست '.config('clients.labels.plural', 'مشتریان');
    $labelPlural = config('clients.labels.plural', 'مشتریان');
    $labelSingular = config('clients.labels.singular', 'مشتری');
    $user = auth()->user();

    $canViewAll      = $user?->hasRole('super-admin')
                        || $user?->can('clients.view.all')
                        || $user?->can('clients.manage');

    $canViewAssigned = $user?->can('clients.view.assigned');
    $canViewOwn      = $user?->can('clients.view.own');

    if ($canViewAll) {
        $visibilityLabel = 'مشاهده تمامی ' . $labelPlural . ' سیستم';
    } elseif ($canViewAssigned) {
        $visibilityLabel = 'مشاهده ' . $labelPlural . ' منتسب یا ایجاد شده توسط شما';
    } elseif ($canViewOwn) {
        $visibilityLabel = 'مشاهده ' . $labelPlural . ' ایجاد شده توسط شما';
    } else {
        $visibilityLabel = 'مشاهده ' . $labelPlural . ' ایجاد شده توسط شما';
    }
    $clientCallsModule = \App\Models\Module::where('slug', 'clientcalls')->first();
    $followUpsModule   = \App\Models\Module::where('slug', 'followups')->first();

    $activeFiltersCount = count(array_filter(request()->only(['search', 'created_by', 'status_id', 'sort']), fn($v) => !empty($v) && $v !== 'newest'));
@endphp

@section('content')
    <div class="space-y-5" x-data="{ selectedIds: [], allChecked: false, bulkStatusId: '', filterOpen: {{ $activeFiltersCount > 0 ? 'true' : 'false' }} }">
        
        {{-- هدر اصلی و نوار ابزار ماژول --}}
        <div class="bg-white dark:bg-gray-800/90 rounded-2xl border border-gray-200/80 dark:border-gray-700/70 p-5 shadow-xs backdrop-blur-sm">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                
                {{-- عنوان و اطلاعات اسکوپ دسترسی --}}
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 shrink-0">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                            <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">
                                {{ request('trashed') == '1' ? 'سطل زباله ' . $labelPlural : $labelPlural }}
                            </h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900/50">
                                {{ $clients->total() }} رکورد
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            {{ request('trashed') == '1' ? 'مشاهده و بازیابی رکوردهای حذف شده' : $visibilityLabel }}
                        </p>
                    </div>
                </div>

                {{-- دکمه‌های عملیات سریع --}}
                <div class="flex items-center gap-2.5 flex-wrap">
                    @if(request('trashed') != '1')
                        {{-- ویجت ایجاد سریع (Livewire) --}}
                        @can('clients.create')
                            @livewire('clients.form', ['asQuickWidget' => true], key('clients-quick-widget'))
                        @endcan

                        {{-- دکمه ایجاد کامل --}}
                        @can('clients.create')
                            @if (Route::has('user.clients.create'))
                                <a href="{{ route('user.clients.create') }}"
                                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 active:scale-95 transition-all duration-200">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ 'افزودن ' . $labelSingular . ' جدید' }}
                                </a>
                            @endif
                        @endcan
                    @endif
                </div>
            </div>

            {{-- نوار دسترسی سریع به زیرمنوهای ماژول مشتریان (Module Hub Sub-Nav) --}}
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between gap-3 overflow-x-auto custom-scrollbar pb-1">
                <div class="flex items-center gap-1.5 text-xs shrink-0">
                    <a href="{{ route('user.clients.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold transition-all {{ !request('trashed') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50' }}">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                        همه {{ $labelPlural }}
                    </a>

                    @can('clients.delete')
                    <a href="{{ route('user.clients.index', ['trashed' => 1]) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold transition-all {{ request('trashed') == '1' ? 'bg-red-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50' }}">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        سطل زباله
                    </a>
                    @endcan

                    @can('clients.manage')
                        @if(Route::has('user.settings.clients.forms'))
                        <a href="{{ route('user.settings.clients.forms') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all">
                            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            فرم‌ساز
                        </a>
                        @endif

                        @if(Route::has('user.settings.clients.statuses'))
                        <a href="{{ route('user.settings.clients.statuses') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            مدیریت وضعیت‌ها
                        </a>
                        @endif

                        @if(Route::has('user.settings.clients.username'))
                        <a href="{{ route('user.settings.clients.username') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all">
                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37c1 .608 2.296.07 2.572-1.065z" /></svg>
                            تنظیمات نام کاربری
                        </a>
                        @endif

                        @if(Route::has('user.settings.clients.auth'))
                        <a href="{{ route('user.settings.clients.auth') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all">
                            <svg class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            ورود پرتال
                        </a>
                        @endif

                        @if(Route::has('user.settings.clients.import'))
                        <a href="{{ route('user.settings.clients.import') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all">
                            <svg class="w-3.5 h-3.5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            ایمپورت / اکسپورت
                        </a>
                        @endif
                    @endcan
                </div>

                {{-- دکمه تاگل فیلتر --}}
                @if(request('trashed') != '1')
                <button @click="filterOpen = !filterOpen"
                        type="button"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold border transition-all shrink-0
                        {{ $activeFiltersCount > 0 ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800' : 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span>فیلتر پیشرفته</span>
                    @if($activeFiltersCount > 0)
                        <span class="w-4 h-4 rounded-full bg-indigo-600 text-white text-[10px] flex items-center justify-center font-black">
                            {{ $activeFiltersCount }}
                        </span>
                    @endif
                </button>
                @endif
            </div>
        </div>

        {{-- پنل فیلتر پیشرفته مدرن --}}
        @if(request('trashed') != '1')
        <div x-show="filterOpen"
             x-collapse
             class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <h2 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                        فیلتر و جستجوی چندگانه
                    </h2>
                </div>
                @if(request()->except('page'))
                    <a href="{{ route('user.clients.index') }}" class="text-xs font-bold text-red-500 hover:text-red-700 flex items-center gap-1 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        پاک کردن فیلترها
                    </a>
                @endif
            </div>
            <div class="p-5">
                <form action="{{ route('user.clients.index') }}" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        {{-- جستجوی متنی --}}
                        <div>
                            <label for="search" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">جستجو</label>
                            <div class="relative">
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                       placeholder="نام، ایمیل، تلفن، کد ملی..."
                                       class="w-full pl-10 pr-3.5 py-2 rounded-xl border-gray-200 bg-gray-50/60 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </div>
                            </div>
                        </div>

                        {{-- فیلتر بر اساس ایجاد کننده --}}
                        <div>
                            <label for="created_by" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">ایجاد کننده</label>
                            <div class="relative">
                                <select name="created_by" id="created_by"
                                        class="w-full appearance-none pl-10 pr-3.5 py-2 rounded-xl border-gray-200 bg-gray-50/60 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                    <option value="">همه کاربران</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected(request('created_by') == $user->id)>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </div>
                            </div>
                        </div>

                        {{-- فیلتر بر اساس وضعیت --}}
                        <div>
                            <label for="status_id" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">وضعیت پرونده</label>
                            <div class="relative">
                                <select name="status_id" id="status_id"
                                        class="w-full appearance-none pl-10 pr-3.5 py-2 rounded-xl border-gray-200 bg-gray-50/60 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                    <option value="">همه وضعیت‌ها</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" @selected(request('status_id') == $status->id)>
                                            {{ $status->label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </div>
                        </div>

                        {{-- ترتیب نمایش --}}
                        <div>
                            <label for="sort" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">مرتب‌سازی</label>
                            <div class="relative">
                                <select name="sort" id="sort"
                                        class="w-full appearance-none pl-10 pr-3.5 py-2 rounded-xl border-gray-200 bg-gray-50/60 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                    <option value="newest" @selected(request('sort', 'newest') == 'newest')>جدیدترین‌ها</option>
                                    <option value="oldest" @selected(request('sort') == 'oldest')>قدیمی‌ترین‌ها</option>
                                    <option value="name_asc" @selected(request('sort') == 'name_asc')>نام (الف تا ی)</option>
                                    <option value="name_desc" @selected(request('sort') == 'name_desc')>نام (ی تا الف)</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M6 8h12m-8 4h4" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700 mt-4">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition-all shadow-sm active:scale-95">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                            اعمال فیلترها
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- پنل عملیات گروهی شناور --}}
        @if(request('trashed') != '1')
        <div x-show="selectedIds.length > 0"
             x-transition
             class="flex flex-wrap items-center justify-between gap-4 p-4 bg-indigo-50/90 dark:bg-indigo-950/50 border border-indigo-200/80 dark:border-indigo-800 rounded-2xl shadow-sm">
            <div class="flex items-center gap-2.5 text-xs text-indigo-900 dark:text-indigo-200">
                <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-black text-xs" x-text="selectedIds.length"></span>
                <span class="font-bold">مورد انتخاب شده است.</span>
            </div>
            
            <form method="POST" action="{{ route('user.clients.bulk-update') }}" class="flex flex-wrap items-center gap-2.5">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>

                <div class="flex items-center gap-2">
                    <select name="status_id" x-model="bulkStatusId" class="rounded-xl border border-indigo-200 dark:border-indigo-700 bg-white dark:bg-gray-900 px-3 py-1.5 text-xs text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        <option value="">تغییر وضعیت به...</option>
                        @foreach($statuses ?? [] as $status)
                            <option value="{{ $status->id }}">{{ $status->label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" name="action" value="status" :disabled="!bulkStatusId" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold transition-all active:scale-95 shadow-xs">
                        اعمال وضعیت
                    </button>
                </div>

                <div class="h-4 w-px bg-indigo-200 dark:bg-indigo-800 hidden sm:block"></div>

                <button type="submit" name="action" value="delete" onclick="return confirm('آیا از حذف گروهی مشتریان انتخاب شده مطمئن هستید؟')" class="px-3.5 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition-all active:scale-95 shadow-xs">
                    حذف گروهی
                </button>
            </form>
        </div>
        @endif

        {{-- جدول داده‌ها --}}
        <div class="bg-white dark:bg-gray-800/90 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xs overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full whitespace-nowrap text-xs text-right">
                    <thead class="bg-gray-50/70 dark:bg-gray-900/60 border-b border-gray-200/80 dark:border-gray-700 text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="w-12 px-4 py-3.5 text-right">
                            <input type="checkbox" x-model="allChecked" @change="selectedIds = allChecked ? [{{ implode(',', $clients->pluck('id')->toArray()) }}] : []" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </th>
                        <th class="px-4 py-3.5 font-bold">#</th>
                        <th class="px-4 py-3.5 font-bold">اطلاعات {{ $labelSingular }}</th>
                        <th class="px-4 py-3.5 font-bold">راه‌های ارتباطی</th>
                        @if($clientCallsModule && $clientCallsModule->installed && $clientCallsModule->active)
                            @can('client-calls.create')
                                <th class="px-4 py-3.5 font-bold">تماس‌ها</th>
                            @endcan
                        @endif
                        @if($followUpsModule && $followUpsModule->installed && $followUpsModule->active)
                            @can('followups.create')
                                <th class="px-4 py-3.5 font-bold">پیگیری‌ها</th>
                            @endcan
                        @endif
                        <th class="px-4 py-3.5 font-bold">وضعیت</th>
                        <th class="px-4 py-3.5 font-bold">ایجاد کننده</th>
                        <th class="px-4 py-3.5 font-bold text-left pl-6">عملیات</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($clients as $client)
                        <tr class="group hover:bg-indigo-50/30 dark:hover:bg-gray-700/20 transition-colors duration-150">
                            <td class="w-12 px-4 py-3.5">
                                <input type="checkbox" :value="{{ $client->id }}" x-model="selectedIds" @change="allChecked = (selectedIds.length === {{ $clients->count() }})" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </td>
                            {{-- ID --}}
                            <td class="px-4 py-3.5 text-gray-400 text-xs">
                                {{ $client->id }}
                            </td>

                            {{-- User Info with Initial Avatar --}}
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900/40 flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ mb_substr($client->full_name ?? 'م', 0, 1) }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <a href="{{ route('user.clients.show', $client) }}" class="font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate">
                                            {{ $client->full_name }}
                                        </a>
                                        <span class="text-xs text-gray-400 dir-ltr text-right truncate">
                                            {{ $client->username ? '@' . $client->username : '' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact --}}
                            <td class="px-4 py-3.5">
                                <div class="flex flex-col gap-1 text-xs">
                                    @if($client->phone)
                                        <div class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300 dir-ltr text-right">
                                            <span class="text-indigo-500 text-[11px]">📞</span> {{ $client->phone }}
                                        </div>
                                    @endif
                                    @if($client->email)
                                        <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 dir-ltr text-right">
                                            <span class="text-gray-400 text-[11px]">✉️</span> {{ $client->email }}
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
                                    <td class="px-4 py-3.5 align-middle text-gray-600 dark:text-gray-300">
                                        @include('clientcalls::components.client-call-manager', ['client' => $client])
                                    </td>
                                @endcan
                            @endif

                            {{-- FollowUps (popup) --}}
                            @if($followUpsModule && $followUpsModule->installed && $followUpsModule->active)
                                @can('followups.create')
                                    <td class="px-4 py-3.5 align-middle text-gray-600 dark:text-gray-300">
                                        @include('followups::components.client-followup-manager', ['client' => $client])
                                    </td>
                                @endcan
                            @endif

                            {{-- Status --}}
                            <td class="px-4 py-3.5">
                                @if($client->status)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border"
                                          style="background-color: {{ $client->status->color }}15; color: {{ $client->status->color }}; border-color: {{ $client->status->color }}30;">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $client->status->color }};"></span>
                                        {{ $client->status->label }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Creator --}}
                            <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400">
                                @if(optional($client->creator)->name)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-[11px]">
                                        {{ $client->creator->name }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    @if(request('trashed') == '1')
                                        @can('clients.delete')
                                            {{-- Restore --}}
                                            <form action="{{ route('user.clients.restore', $client->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit"
                                                        class="p-1.5 rounded-xl text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/40 transition-colors"
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
                                                        class="p-1.5 rounded-xl text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40 transition-colors"
                                                        title="حذف دائمی">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    @else
                                        @can('clients.view')
                                            <a href="{{ route('user.clients.show', $client) }}"
                                               class="p-1.5 rounded-xl text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/40 transition-colors"
                                               title="مشاهده پرونده">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                        @endcan

                                        @if(isset($isBookingQueueEnabled) && $isBookingQueueEnabled)
                                            <button type="button"
                                                    @click="$dispatch('open-waitlist-modal', { clientId: {{ $client->id }} })"
                                                    class="p-1.5 rounded-xl text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-950/40 transition-colors cursor-pointer"
                                                    title="قرار دادن در صف انتظار">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        @endif

                                        @can('clients.edit')
                                            <a href="{{ route('user.clients.edit', $client) }}"
                                               class="p-1.5 rounded-xl text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700/50 transition-colors"
                                               title="ویرایش">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endcan

                                        @can('clients.delete')
                                            <form action="{{ route('user.clients.destroy', $client) }}" method="POST"
                                                  onsubmit="return confirm('آیا از انتقال این مشتری به سطل زباله اطمینان دارید؟');"
                                                  class="inline-block">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 rounded-xl text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40 transition-colors"
                                                        title="انتقال به سطل زباله">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-14 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-50 dark:bg-gray-800/80 flex items-center justify-center text-gray-400 mb-3 border border-gray-100 dark:border-gray-700">
                                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200">هیچ موردی یافت نشد</p>
                                    <p class="text-xs text-gray-400 mt-1">با معیارهای جستجوی فعلی نتیجه‌ای موجود نیست یا هنوز رکوردی ثبت نشده است.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- صفحه‌بندی --}}
            @if($clients->hasPages())
                <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/30">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>
    </div>

    @if(isset($isBookingQueueEnabled) && $isBookingQueueEnabled)
        @livewire('booking.user.booking-waitlist-modal')
    @endif
@endsection
