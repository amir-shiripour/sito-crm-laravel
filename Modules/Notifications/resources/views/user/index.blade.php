@extends('layouts.user')

@php
    use Morilog\Jalali\Jalalian;

    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-md";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl";
@endphp

@section('title', 'اعلان‌های من')

@section('content')
    <div class="w-full mx-auto px-4 py-8 space-y-6" x-data="notificationsPage()">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </span>
                    اعلان‌های من
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">
                    مرکز دریافت و مدیریت پیام‌ها، یادآوری‌ها و هشدارهای کاری شما در سامانه
                </p>
            </div>

            <div class="flex items-center gap-2">
                @if(Route::has('user.notifications.settings'))
                    <a href="{{ route('user.notifications.settings') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition-all shadow-sm">
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>تنظیمات دریافت</span>
                    </a>
                @endif

                @if($unreadCount > 0)
                    <button @click="markAllRead('{{ route('user.notifications.mark-all-read') }}')"
                            type="button"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:text-indigo-400 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/30 border border-indigo-100/30 dark:border-indigo-900/30 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>خواندن همه</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- تب‌ها و فیلترهای پیشرفته --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-sm p-4 space-y-4">
            
            {{-- تب وضعیت‌ها --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <div class="flex gap-2 flex-wrap">
                    <a href="{{ route('user.notifications.index', array_merge(request()->except('page', 'filter'), ['filter' => 'all'])) }}"
                       class="px-3 py-1.5 text-xs font-bold rounded-xl transition-all {{ ($statusFilter ?? 'all') === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-50 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        همه ({{ $totalCount }})
                    </a>
                    <a href="{{ route('user.notifications.index', array_merge(request()->except('page', 'filter'), ['filter' => 'unread'])) }}"
                       class="px-3 py-1.5 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 {{ ($statusFilter ?? 'all') === 'unread' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-50 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <span>خوانده نشده</span>
                        @if($unreadCount > 0)
                            <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ ($statusFilter ?? 'all') === 'unread' ? 'bg-white text-indigo-700' : 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/60 dark:text-indigo-300' }}">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('user.notifications.index', array_merge(request()->except('page', 'filter'), ['filter' => 'read'])) }}"
                       class="px-3 py-1.5 text-xs font-bold rounded-xl transition-all {{ ($statusFilter ?? 'all') === 'read' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-50 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        خوانده شده ({{ $readCount }})
                    </a>
                </div>

                {{-- جستجوی فوری --}}
                <form action="{{ route('user.notifications.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="filter" value="{{ $statusFilter }}">
                    <input type="hidden" name="category" value="{{ $categoryFilter }}">
                    <input type="hidden" name="priority" value="{{ $priorityFilter }}">
                    
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="جستجو در اعلان‌ها..."
                               class="w-48 sm:w-64 text-xs rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900/80 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 pl-8 focus:ring-indigo-500 focus:border-indigo-500">
                        <button type="submit" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>

                    @if($search || $categoryFilter !== 'all' || $priorityFilter !== 'all')
                        <a href="{{ route('user.notifications.index') }}" title="پاک کردن فیلترها"
                           class="p-2 rounded-xl text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </form>
            </div>

            {{-- ردیف فیلتر دسته‌بندی و اولویت --}}
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <span class="font-bold text-gray-500 dark:text-gray-400">دسته‌بندی:</span>
                <a href="{{ route('user.notifications.index', array_merge(request()->except('page', 'category'), ['category' => 'all'])) }}"
                   class="px-2.5 py-1 rounded-lg transition-colors {{ ($categoryFilter ?? 'all') === 'all' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300 font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/60' }}">
                    همه دسته‌ها
                </a>
                @foreach($categories as $cKey => $cVal)
                    <a href="{{ route('user.notifications.index', array_merge(request()->except('page', 'category'), ['category' => $cKey])) }}"
                       class="px-2.5 py-1 rounded-lg transition-colors {{ ($categoryFilter ?? 'all') === $cKey ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300 font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/60' }}">
                        {{ $cVal['label'] }}
                    </a>
                @endforeach

                <div class="mr-auto flex items-center gap-2">
                    <span class="font-bold text-gray-500 dark:text-gray-400">اولویت:</span>
                    <select onchange="window.location.href=this.value"
                            class="text-xs rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-200 py-1 pr-6 pl-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="{{ route('user.notifications.index', array_merge(request()->except('page', 'priority'), ['priority' => 'all'])) }}" {{ ($priorityFilter ?? 'all') === 'all' ? 'selected' : '' }}>همه اولویت‌ها</option>
                        @foreach($priorities as $pKey => $pVal)
                            <option value="{{ route('user.notifications.index', array_merge(request()->except('page', 'priority'), ['priority' => $pKey])) }}" {{ ($priorityFilter ?? 'all') === $pKey ? 'selected' : '' }}>{{ $pVal['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>

        {{-- نوار شناور عملیات گروهی (Bulk Actions) --}}
        <div x-show="selectedIds.length > 0" x-transition
             class="sticky top-20 z-20 bg-indigo-900 text-white rounded-2xl p-3 px-5 shadow-lg flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold bg-white/20 px-2.5 py-1 rounded-lg">
                    <span x-text="selectedIds.length"></span> مورد انتخاب شد
                </span>
                <span class="text-xs opacity-80">عملیات گروهی روی موارد انتخابی:</span>
            </div>

            <div class="flex items-center gap-2">
                <button @click="bulkMarkAsRead('{{ route('user.notifications.bulk-read') }}')"
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl bg-white/10 hover:bg-white/20 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>علامت‌گذاری به عنوان خوانده شده</span>
                </button>

                <button @click="openBulkDeleteModal('{{ route('user.notifications.bulk-destroy') }}')"
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl bg-red-500/80 hover:bg-red-600 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>حذف انتخاب‌شده‌ها</span>
                </button>

                <button @click="selectedIds = []" type="button" class="text-xs text-white/70 hover:text-white px-2">
                    انصراف
                </button>
            </div>
        </div>

        {{-- چک‌باکس انتخاب همه --}}
        @if($notifications->count() > 0)
            <div class="flex items-center justify-between px-2 text-xs text-gray-500 dark:text-gray-400">
                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" @change="toggleSelectAll($event)"
                           :checked="selectedIds.length > 0 && selectedIds.length === totalItemsOnPage"
                           class="w-4 h-4 rounded text-indigo-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-indigo-500">
                    <span class="font-semibold">انتخاب همه موارد این صفحه</span>
                </label>
                <span>نمایش {{ $notifications->firstItem() }} تا {{ $notifications->lastItem() }} از {{ $notifications->total() }} اعلان</span>
            </div>
        @endif

        {{-- لیست اعلان‌ها --}}
        <div class="space-y-3">
            @forelse($notifications as $n)
                @php
                    $data = $n->formatted_data;
                    $isUnread = is_null($n->read_at);
                    $priority = $n->priority;
                    $severity = $n->severity;
                    $category = $n->category;

                    $bgColor = $isUnread 
                        ? 'bg-indigo-50/50 dark:bg-indigo-950/30 border-indigo-200 dark:border-indigo-800/60 shadow-sm' 
                        : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/70';

                    $iconColorMap = [
                        'danger'  => 'bg-red-50 text-red-600 dark:bg-red-950/50 dark:text-red-400 border-red-100 dark:border-red-900/40',
                        'warning' => 'bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400 border-amber-100 dark:border-amber-900/40',
                        'success' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/40',
                        'info'    => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400 border-indigo-100 dark:border-indigo-900/40',
                    ];
                    $iconClass = $iconColorMap[$severity] ?? $iconColorMap['info'];
                @endphp

                <div id="notification-{{ $n->id }}"
                     class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-4 rounded-2xl border transition-all duration-200 hover:shadow-md {{ $bgColor }}">
                    
                    <div class="flex items-start gap-3.5 min-w-0 flex-1">
                        {{-- چک‌باکس انتخاب آیتم --}}
                        <div class="pt-2.5 flex-shrink-0">
                            <input type="checkbox" value="{{ $n->id }}" x-model="selectedIds"
                                   class="w-4 h-4 rounded text-indigo-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-indigo-500 cursor-pointer">
                        </div>

                        {{-- آیکون متناسب با سطح اهمیت --}}
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center border {{ $iconClass }}">
                            @if($severity === 'danger' || $priority === 'urgent')
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            @elseif($severity === 'warning' || $priority === 'high')
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @elseif($severity === 'success')
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @endif
                        </div>

                        {{-- متن پیام و برچسب‌ها --}}
                        <div class="space-y-1.5 min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                    {{ $n->title }}
                                </span>

                                @if($isUnread)
                                    <span class="unread-dot inline-flex w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                @endif

                                {{-- برچسب دسته‌بندی --}}
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-gray-100 text-gray-600 dark:bg-gray-700/80 dark:text-gray-300">
                                    {{ $n->category_label }}
                                </span>

                                {{-- برچسب اولویت --}}
                                @if($priority === 'urgent')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                        فوری
                                    </span>
                                @elseif($priority === 'high')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                        مهم
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                                {{ $n->message }}
                            </p>

                            @if($n->action_url)
                                <a href="{{ $n->action_url }}" class="inline-flex items-center gap-1 mt-1 text-[11px] font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    <span>مشاهده مورد مرتبط</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- زمان و عملیات تک‌موردی --}}
                    <div class="flex md:flex-col items-center md:items-end justify-between w-full md:w-auto gap-3 pt-3 md:pt-0 border-t md:border-0 border-gray-100 dark:border-gray-700/60">
                        <div class="text-[10px] text-gray-400 dark:text-gray-400 font-medium whitespace-nowrap" dir="rtl">
                            {{ $n->created_at->diffForHumans() }}
                            <span class="opacity-75">({{ Jalalian::fromCarbon($n->created_at)->format('H:i - Y/m/d') }})</span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            @if($isUnread)
                                <button @click.stop.prevent="markAsRead('{{ route('user.notifications.mark-read', $n->id) }}', '{{ $n->id }}', $el)"
                                        type="button"
                                        title="علامت‌گذاری به عنوان خوانده شده"
                                        class="mark-read-btn p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            @endif
                            <button @click.stop.prevent="openSingleDeleteModal('{{ route('user.notifications.destroy', $n->id) }}', '{{ $n->id }}')"
                                    type="button"
                                    title="حذف اعلان"
                                    class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/40 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                </div>
            @empty
                <div class="flex flex-col items-center justify-center p-12 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/70 shadow-sm text-center">
                    <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-900/50 flex items-center justify-center text-gray-300 dark:text-gray-500 mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v4.5m16 3H4" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">هیچ اعلانی یافت نشد</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 max-w-sm">
                        با توجه به فیلترهای انتخابی، هیچ پیامی برای نمایش وجود ندارد.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- صفحه‌بندی --}}
        @if($notifications->hasPages())
            <div class="pt-4">
                {{ $notifications->links() }}
            </div>
        @endif

        {{-- مودال اختصاصی تایید حذف پنل (Panel Alert Modal) --}}
        <div x-cloak x-show="deleteModal.open"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 text-center space-y-4 transform transition-all"
                 @click.outside="closeDeleteModal()"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="scale-95 opacity-0"
                 x-transition:enter-end="scale-100 opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="scale-100 opacity-100"
                 x-transition:leave-end="scale-95 opacity-0">

                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400 flex items-center justify-center mx-auto border border-rose-100 dark:border-rose-900/30">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>

                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white" x-text="deleteModal.title"></h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed" x-text="deleteModal.message"></p>
                </div>

                <div class="flex items-center justify-center gap-3 pt-3">
                    <button type="button" @click="closeDeleteModal()" :disabled="deleteModal.loading"
                            class="w-1/2 py-2.5 text-xs font-bold rounded-xl text-gray-700 bg-gray-100 hover:bg-gray-200 dark:text-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 transition-colors">
                        انصراف
                    </button>
                    <button type="button" @click="confirmDeleteAction()" :disabled="deleteModal.loading"
                            class="w-1/2 py-2.5 text-xs font-bold rounded-xl text-white bg-rose-600 hover:bg-rose-700 transition-colors shadow-sm inline-flex items-center justify-center gap-1.5">
                        <span x-show="!deleteModal.loading">بله، حذف شود</span>
                        <span x-show="deleteModal.loading">در حال حذف...</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
function notificationsPage() {
    return {
        selectedIds: [],
        totalItemsOnPage: {{ $notifications->count() }},
        pageNotificationIds: @json($notifications->pluck('id')),

        deleteModal: {
            open: false,
            loading: false,
            url: null,
            id: null,
            isBulk: false,
            title: '',
            message: ''
        },

        openSingleDeleteModal(url, id) {
            this.deleteModal.open = true;
            this.deleteModal.loading = false;
            this.deleteModal.url = url;
            this.deleteModal.id = id;
            this.deleteModal.isBulk = false;
            this.deleteModal.title = 'حذف اعلان';
            this.deleteModal.message = 'آیا از حذف این اعلان اطمینان دارید؟ این عملیات غیرقابل بازگشت است.';
        },

        openBulkDeleteModal(url) {
            if (this.selectedIds.length === 0) return;
            this.deleteModal.open = true;
            this.deleteModal.loading = false;
            this.deleteModal.url = url;
            this.deleteModal.id = null;
            this.deleteModal.isBulk = true;
            this.deleteModal.title = 'حذف گروهی اعلان‌ها';
            this.deleteModal.message = `آیا از حذف ${this.selectedIds.length} اعلان انتخاب شده اطمینان دارید؟ این عملیات غیرقابل بازگشت است.`;
        },

        closeDeleteModal() {
            if (this.deleteModal.loading) return;
            this.deleteModal.open = false;
            this.deleteModal.id = null;
            this.deleteModal.url = null;
        },

        async confirmDeleteAction() {
            if (!this.deleteModal.url) return;
            this.deleteModal.loading = true;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            try {
                const body = this.deleteModal.isBulk ? JSON.stringify({ ids: this.selectedIds }) : null;
                const response = await fetch(this.deleteModal.url, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: body
                });

                const data = await response.json();

                if (data.success) {
                    if (window.showToast) {
                        window.showToast('success', data.message);
                    }

                    if (this.deleteModal.isBulk) {
                        this.selectedIds.forEach(id => {
                            const card = document.getElementById(`notification-${id}`);
                            if (card) card.remove();
                        });
                        this.selectedIds = [];
                        setTimeout(() => window.location.reload(), 500);
                    } else {
                        const id = this.deleteModal.id;
                        const card = document.getElementById(`notification-${id}`);
                        if (card) {
                            card.style.transition = 'all 0.3s ease';
                            card.style.opacity = '0';
                            card.style.transform = 'translateY(10px)';
                            setTimeout(() => card.remove(), 300);
                        }
                    }
                    this.closeDeleteModal();
                } else {
                    if (window.showToast) {
                        window.showToast('error', data.message || 'خطا در حذف اعلان');
                    }
                    this.deleteModal.loading = false;
                }
            } catch (err) {
                console.error(err);
                if (window.showToast) {
                    window.showToast('error', 'ارتباط با سرور برقرار نشد.');
                }
                this.deleteModal.loading = false;
            }
        },

        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selectedIds = [...this.pageNotificationIds];
            } else {
                this.selectedIds = [];
            }
        },

        markAsRead(url, id, el) {
            if (el) el.disabled = true;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const card = document.getElementById(`notification-${id}`);
                    if (card) {
                        card.classList.remove('bg-indigo-50/50', 'dark:bg-indigo-950/30', 'border-indigo-200', 'dark:border-indigo-800/60', 'shadow-sm');
                        card.classList.add('bg-white', 'dark:bg-gray-800', 'border-gray-200', 'dark:border-gray-700/70');
                        const dot = card.querySelector('.unread-dot');
                        if (dot) dot.remove();
                    }
                    if (el) el.remove();
                    if (window.showToast) window.showToast('success', data.message);
                } else {
                    if (el) el.disabled = false;
                    if (window.showToast) window.showToast('error', data.message || 'خطا در ثبت');
                }
            })
            .catch(err => {
                if (el) el.disabled = false;
                console.error(err);
                if (window.showToast) window.showToast('error', 'ارتباط با سرور برقرار نشد.');
            });
        },

        markAllRead(url) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (window.showToast) window.showToast('success', data.message);
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    if (window.showToast) window.showToast('error', data.message || 'خطا در ثبت');
                }
            });
        },

        bulkMarkAsRead(url) {
            if (this.selectedIds.length === 0) return;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ ids: this.selectedIds })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (window.showToast) window.showToast('success', data.message);
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    if (window.showToast) window.showToast('error', data.message || 'خطا در ثبت');
                }
            });
        }
    }
}
</script>
@endpush
