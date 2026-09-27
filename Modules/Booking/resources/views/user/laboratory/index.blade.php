<div>
    <style>
        .tooth-path {
            cursor: pointer;
            transition: fill .14s ease, stroke .14s ease, filter .14s ease;
            stroke-width: 1.5px;
            vector-effect: non-scaling-stroke;
        }
        .tooth-selected {
            fill: #4f46e5 !important;
            stroke: #4338ca !important;
            stroke-width: 2.5px !important;
            filter: drop-shadow(0 2px 6px rgba(79, 70, 229, 0.45));
        }
        .dark .tooth-selected {
            fill: #6366f1 !important;
            stroke: #818cf8 !important;
        }
        .tooth-unselected {
            fill: #ffffff !important;
            stroke: #cbd5e1 !important;
        }
        .dark .tooth-unselected {
            fill: #1e293b !important;
            stroke: #475569 !important;
        }
        .tooth-unselected:hover {
            fill: #eef2ff !important;
            stroke: #6366f1 !important;
        }
        .dark .tooth-unselected:hover {
            fill: #312e81 !important;
            stroke: #818cf8 !important;
        }
    </style>
    <div class="space-y-6">
        {{-- هدر کارتابل لابراتوار --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </span>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        مدیریت سفارشات و پیگیری‌های لابراتوار
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-300 mt-1">
                        نظارت بر فرآیند ساخت پروتز، تاریخ‌های پیگیری خودکار و هشدارهای سررسید
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                {{-- دکمه انتقال به برگه پیگیری روز --}}
                <a href="{{ route('user.booking.laboratory.daily-board') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-md shadow-amber-500/20 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                    برگه پیگیری روز (Daily Board)
                </a>

                {{-- دکمه ثبت سفارش جدید --}}
                <button type="button" wire:click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/30 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    ثبت سفارش جدید لابراتوار
                </button>

                <a href="{{ route('user.booking.dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 text-xs font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    داشبورد نوبت‌دهی
                </a>
            </div>
        </div>

        {{-- اعلان‌های موفقیت و خطا --}}
        @if($toastSuccess)
            <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 p-4 border border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    {{ $toastSuccess }}
                </div>
                <button type="button" wire:click="$set('toastSuccess', null)" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200 text-base">&times;</button>
            </div>
        @endif

        @if($toastError)
            <div class="rounded-2xl bg-rose-50 dark:bg-rose-950/30 p-4 border border-rose-200 dark:border-rose-800/40 text-rose-800 dark:text-rose-300 text-xs font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ $toastError }}
                </div>
                <button type="button" wire:click="$set('toastError', null)" class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-200 text-base">&times;</button>
            </div>
        @endif

        {{-- راهنمای رنگ‌بندی سیستم --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xs">
                <div class="w-3.5 h-3.5 rounded-full bg-rose-500 shadow-sm shadow-rose-500/50"></div>
                <div class="text-xs">
                    <span class="font-bold text-rose-600 dark:text-rose-400">ردیف قرمز:</span>
                    <span class="text-slate-600 dark:text-slate-300">از زمان تایید شده گذشته / تاخیر در دریافت</span>
                </div>
            </div>

            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xs">
                <div class="w-3.5 h-3.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></div>
                <div class="text-xs">
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">ردیف سبز:</span>
                    <span class="text-slate-600 dark:text-slate-300">کار از لابراتوار تحویل مطب گردید</span>
                </div>
            </div>

            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xs">
                <div class="w-3.5 h-3.5 rounded-full bg-indigo-500 shadow-sm shadow-indigo-500/50"></div>
                <div class="text-xs">
                    <span class="font-bold text-indigo-600 dark:text-indigo-400">ردیف عادی:</span>
                    <span class="text-slate-600 dark:text-slate-300">در حال طی مراحل پیش‌بینی شده</span>
                </div>
            </div>
        </div>

        {{-- تب‌ها و فیلترها --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 space-y-4">
            {{-- تب‌های اصلی --}}
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                <div class="flex flex-wrap gap-1.5 text-xs font-bold">
                    @foreach($labTypes as $lt)
                        <button type="button" wire:click="$set('activeTab', '{{ $lt['id'] }}')"
                                class="px-4 py-2 rounded-xl transition-all {{ $activeTab === $lt['id'] ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700/60' }}">
                            {{ $lt['name'] }}
                        </button>
                    @endforeach

                    <button type="button" wire:click="$set('activeTab', 'received')"
                            class="px-4 py-2 rounded-xl transition-all {{ $activeTab === 'received' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700/60' }}">
                        تحویل گرفته شده‌ها (سبز)
                    </button>

                    <button type="button" wire:click="$set('activeTab', 'all')"
                            class="px-4 py-2 rounded-xl transition-all {{ $activeTab === 'all' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700/60' }}">
                        تمام سفارشات
                    </button>
                </div>

                {{-- جستجوی زنده --}}
                <div class="w-full sm:w-72 relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="جستجوی نام بیمار، پرونده یا شماره سفارش..."
                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3.5 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all pl-9">
                    <svg class="w-4 h-4 absolute left-3 top-2.5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            {{-- فیلترهای دسته و وضعیت --}}
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <span class="text-gray-600 dark:text-gray-400 font-bold">دسته‌بندی:</span>
                <select wire:model.live="categoryFilter" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-1.5 text-xs text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all">
                    <option value="all">تمام دسته‌ها</option>
                    @foreach($allCategories as $catId => $catName)
                        <option value="{{ $catId }}">{{ $catName }}</option>
                    @endforeach
                </select>

                <span class="text-gray-600 dark:text-gray-400 font-bold mr-3">وضعیت:</span>
                <select wire:model.live="statusFilter" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-1.5 text-xs text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all">
                    <option value="all">همه</option>
                    <option value="in_progress">در جریان</option>
                    <option value="received">تحویل شده</option>
                </select>
            </div>
        </div>

        {{-- جدول اصلی سفارشات --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-gray-50/80 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold">
                            <th class="py-3 px-4">ردیف</th>
                            <th class="py-3 px-4">کد سفارش</th>
                            <th class="py-3 px-4">نام بیمار و شماره پرونده</th>
                            <th class="py-3 px-4">نوع کار و تعداد</th>
                            <th class="py-3 px-4">لابراتوار</th>
                            <th class="py-3 px-4">تاریخ ارسال</th>
                            <th class="py-3 px-4">مراحل پیگیری و زمان‌بندی</th>
                            <th class="py-3 px-4">موعد تحویل</th>
                            <th class="py-3 px-4 text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200/80 dark:divide-gray-700">
                        @forelse($orders as $index => $order)
                            @php
                                $isOverdue = $order->isOverdue();
                                $isReceived = $order->isReceived();

                                // تعیین کلاس ردیف قرمز یا سبز یا عادی با ارگونومی عالی در تم روشن و تاریک
                                $rowClass = 'transition-all duration-150 ';
                                if ($isReceived) {
                                    $rowClass .= 'bg-emerald-50/70 dark:bg-emerald-950/30 border-r-4 border-emerald-500 hover:bg-emerald-50/90 dark:hover:bg-emerald-950/40';
                                } elseif ($isOverdue) {
                                    $rowClass .= 'bg-rose-50/75 dark:bg-rose-950/35 border-r-4 border-rose-500 hover:bg-rose-50/90 dark:hover:bg-rose-950/45';
                                } else {
                                    $rowClass .= 'hover:bg-gray-50/80 dark:hover:bg-gray-700/40';
                                }
                            @endphp

                            <tr class="{{ $rowClass }}">
                                <td class="py-3.5 px-4 font-bold text-gray-700 dark:text-gray-300">
                                    {{ $orders->firstItem() + $index }}
                                </td>

                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $order->order_number }}</span>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $order->patient_name }}</div>
                                    @if($order->patient_file_number)
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">پرونده: {{ $order->patient_file_number }}</div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700/70 dark:text-gray-200 border border-gray-200/50 dark:border-gray-600/50">
                                                {{ $order->category_label }}
                                            </span>
                                            @if($order->units_count > 0)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/40">
                                                    {{ $order->units_count }} واحد
                                                </span>
                                            @endif
                                        </div>

                                        {{-- نمایش کراس‌گرید پالمر دندان‌ها با امکان کلیک سریع برای باز کردن تب نقشه دندانی --}}
                                        @if($order->has_teeth)
                                            @php
                                                $groupedTeeth = $order->grouped_teeth;
                                            @endphp
                                            <button type="button"
                                                    wire:click="openDetailModal({{ $order->id }}, 'dental_chart')"
                                                    title="مشاهده نقشه دندانی کامل و مشخصات واحدها"
                                                    class="inline-flex items-center gap-2 border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 hover:bg-indigo-50/70 dark:hover:bg-indigo-950/40 hover:border-indigo-300 dark:hover:border-indigo-600/60 px-2.5 py-1 rounded-xl shadow-2xs select-none transition-all cursor-pointer group text-right">
                                                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 pl-1.5 border-l border-slate-200 dark:border-slate-700 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">دندان:</span>
                                                <div class="inline-grid grid-cols-2 text-center align-middle">
                                                    {{-- UR --}}
                                                    <div class="border-l-2 border-b-2 border-slate-300 dark:border-slate-600 py-0.5 px-1.5 flex items-center justify-end gap-1.5 min-w-[22px] min-h-[18px]">
                                                        @foreach($groupedTeeth['UR'] as $t)
                                                            <span class="inline-flex items-center justify-center min-w-[10px] text-xs font-black text-indigo-600 dark:text-indigo-400">{{ $t['num'] }}</span>
                                                        @endforeach
                                                    </div>
                                                    {{-- UL --}}
                                                    <div class="border-b-2 border-slate-300 dark:border-slate-600 py-0.5 px-1.5 flex items-center justify-start gap-1.5 min-w-[22px] min-h-[18px]">
                                                        @foreach($groupedTeeth['UL'] as $t)
                                                            <span class="inline-flex items-center justify-center min-w-[10px] text-xs font-black text-indigo-600 dark:text-indigo-400">{{ $t['num'] }}</span>
                                                        @endforeach
                                                    </div>
                                                    {{-- LR --}}
                                                    <div class="border-l-2 border-slate-300 dark:border-slate-600 py-0.5 px-1.5 flex items-center justify-end gap-1.5 min-w-[22px] min-h-[18px]">
                                                        @foreach($groupedTeeth['LR'] as $t)
                                                            <span class="inline-flex items-center justify-center min-w-[10px] text-xs font-black text-indigo-600 dark:text-indigo-400">{{ $t['num'] }}</span>
                                                        @endforeach
                                                    </div>
                                                    {{-- LL --}}
                                                    <div class="py-0.5 px-1.5 flex items-center justify-start gap-1.5 min-w-[22px] min-h-[18px]">
                                                        @foreach($groupedTeeth['LL'] as $t)
                                                            <span class="inline-flex items-center justify-center min-w-[10px] text-xs font-black text-indigo-600 dark:text-indigo-400">{{ $t['num'] }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </button>
                                        @elseif($order->teeth_numbers)
                                            <button type="button"
                                                    wire:click="openDetailModal({{ $order->id }}, 'dental_chart')"
                                                    title="مشاهده نقشه دندانی"
                                                    class="text-[11px] text-indigo-600 dark:text-indigo-400 font-semibold hover:underline block">
                                                دندان: {{ $order->teeth_numbers }}
                                            </button>
                                        @endif
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 font-medium text-gray-700 dark:text-gray-300">
                                    {{ $order->lab_partner_name ?: 'نامشخص' }}
                                </td>

                                <td class="py-3.5 px-4 text-gray-700 dark:text-gray-300">
                                    {{ $order->sent_at_jalali }}
                                </td>

                                {{-- ستون هوشمند و فشرده مراحل پیگیری و پیشرفت کار --}}
                                <td class="py-3.5 px-4 min-w-[210px]">
                                    @php
                                        $currentStage = $order->current_stage;
                                        $nextStage = $order->next_stage;
                                        $totalStages = $order->total_stages_count;
                                        $completedStages = $order->completed_stages_count;
                                        $progressPercent = $order->progress_percentage;
                                    @endphp

                                    @if($totalStages > 0)
                                        <div class="space-y-1.5 max-w-[240px]">
                                            {{-- نوار پیشرفت مینیاتوری --}}
                                            <div class="flex items-center justify-between text-[10px] text-slate-500 dark:text-slate-400">
                                                <span class="font-bold">پیشرفت: {{ $completedStages }} از {{ $totalStages }} مرحله</span>
                                                <span class="font-black text-indigo-600 dark:text-indigo-400">{{ $progressPercent }}٪</span>
                                            </div>
                                            <div class="w-full bg-slate-100 dark:bg-slate-700/60 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-indigo-600 dark:bg-indigo-500 h-1.5 rounded-full transition-all duration-300" style="width: {{ $progressPercent }}%"></div>
                                            </div>

                                            {{-- مرحله جاری --}}
                                            @if($currentStage)
                                                @php
                                                    $stOverdue = $currentStage->isOverdue();
                                                    $stDueToday = $currentStage->isDueToday();
                                                    $badgeBg = $stOverdue 
                                                        ? 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/50 animate-pulse' 
                                                        : ($stDueToday 
                                                            ? 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/50' 
                                                            : 'bg-indigo-50/90 text-indigo-800 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800/50');
                                                @endphp
                                                <div class="flex items-center justify-between gap-1 p-1.5 rounded-xl border {{ $badgeBg }}">
                                                    <div class="flex items-center gap-1.5 min-w-0">
                                                        <span class="w-2 h-2 rounded-full {{ $stOverdue ? 'bg-rose-500' : ($stDueToday ? 'bg-amber-500' : 'bg-indigo-500') }} shrink-0"></span>
                                                        <span class="text-xs font-bold truncate" title="{{ $currentStage->stage_title }}">
                                                            {{ $currentStage->stage_title }}
                                                        </span>
                                                    </div>
                                                    <button type="button" 
                                                            wire:click="openStageModal({{ $currentStage->id }})"
                                                            title="تایید و تکمیل مرحله (موعد: {{ $currentStage->due_at_jalali }})"
                                                            class="px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white transition-all shrink-0">
                                                        {{ $currentStage->due_at_jalali }} ↵
                                                    </button>
                                                </div>

                                                {{-- مرحله بعدی --}}
                                                @if($nextStage)
                                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center gap-1 pr-1 truncate">
                                                        <span class="text-slate-400 dark:text-slate-500">بعدی:</span>
                                                        <span class="font-medium truncate">{{ $nextStage->stage_title }}</span>
                                                        <span class="opacity-75 shrink-0">({{ $nextStage->due_at_jalali }})</span>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                    تمام مراحل انجام شد
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500">مرحله‌ای ثبت نشده</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900 dark:text-white">
                                        {{ $order->expected_delivery_at_jalali ?: 'نامشخص' }}
                                    </div>
                                    @if($isReceived)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border dark:border-emerald-800/40 mt-1">
                                            دریافت شده در مطب
                                        </span>
                                    @elseif($isOverdue)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 dark:border dark:border-rose-800/40 mt-1">
                                            دارای تاخیر
                                        </span>
                                    @endif
                                </td>

                                {{-- دکمه‌های عملیات --}}
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- دکمه مشاهده جزئیات کامل سفارش --}}
                                        <button type="button"
                                                wire:click="openDetailModal({{ $order->id }})"
                                                title="مشاهده جزئیات کامل سفارش"
                                                class="p-1.5 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>

                                        @if(!$isReceived)
                                            <button type="button"
                                                    wire:click="openReceiveModal({{ $order->id }})"
                                                    title="ثبت دریافت کار از لابراتوار (سبز شدن)"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white font-bold text-[11px] shadow-sm shadow-emerald-500/20 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                دریافت کار
                                            </button>
                                        @endif

                                        {{-- دکمه ویرایش سفارش --}}
                                        <button type="button"
                                                wire:click="openEditModal({{ $order->id }})"
                                                title="ویرایش مشخصات سفارش"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <button type="button"
                                                wire:click="deleteOrder({{ $order->id }})"
                                                wire:confirm="آیا از حذف این سفارش لابراتوار اطمینان دارید؟"
                                                title="حذف سفارش"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-gray-400 dark:text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-40 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                    هیچ سفارشی مطابق فیلترهای انتخابی یافت نشد.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- صفحه‌بندی --}}
            @if($orders->hasPages())
                <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- مودال ثبت سفارش جدید لابراتوار --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/70 dark:bg-gray-900/50">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center">
                            @if($editingOrderId)
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            @endif
                        </span>
                        {{ $editingOrderId ? "ویرایش سفارش لابراتوار ({$orderNumber})" : "ثبت سفارش جدید لابراتوار ({$orderNumber})" }}
                    </h3>
                    <button type="button" wire:click="$set('showCreateModal', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl font-bold">&times;</button>
                </div>

                <div class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                    {{-- انتخاب یا جستجوی هوشمند بیمار --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">انتخاب یا جستجوی بیمار *</label>

                        @if($selectedClient)
                            {{-- کارت مشخص و شاخص بیمار انتخاب شده --}}
                            <div class="p-4 rounded-2xl border-2 border-indigo-500/40 bg-indigo-50/50 dark:bg-indigo-950/20 dark:border-indigo-500/30 flex items-center justify-between gap-4 transition-all">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-indigo-500/25 shrink-0">
                                        {{ mb_substr($selectedClient['full_name'], 0, 1) }}
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">{{ $selectedClient['full_name'] }}</h4>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                                                بیمار انتخاب شده از پرونده‌ها
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-600 dark:text-slate-300">
                                            @if(!empty($selectedClient['case_number']))
                                                <span class="flex items-center gap-1">
                                                    <span class="text-gray-500 dark:text-gray-400 font-bold">پرونده:</span>
                                                    <span>{{ $selectedClient['case_number'] }}</span>
                                                </span>
                                            @endif
                                            @if(!empty($selectedClient['national_code']))
                                                <span class="flex items-center gap-1">
                                                    <span class="text-gray-500 dark:text-gray-400 font-bold">کد ملی:</span>
                                                    <span>{{ $selectedClient['national_code'] }}</span>
                                                </span>
                                            @endif
                                            @if(!empty($selectedClient['phone']))
                                                <span class="flex items-center gap-1">
                                                    <span class="text-gray-500 dark:text-gray-400 font-bold">تماس:</span>
                                                    <span>{{ $selectedClient['phone'] }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <button type="button" wire:click="clearSelectedClient"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white dark:bg-gray-800 border border-rose-200 dark:border-rose-800/40 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-xs font-bold transition-colors shrink-0 shadow-xs">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    تغییر / حذف انتخاب
                                </button>
                            </div>
                        @else
                            {{-- فیلد جستجوی زنده با کدملی، نام، تلفن یا پرونده --}}
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.250ms="clientSearchQuery"
                                       placeholder="جستجو بر اساس نام، کدملی، شماره تماس یا شماره پرونده بیمار..."
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all pr-9">
                                <svg class="w-4 h-4 absolute right-3 top-3 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>

                                @if(!empty($clientResults))
                                    <div class="absolute right-0 left-0 top-full mt-1 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xl z-30 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700 max-h-64 overflow-y-auto">
                                        @foreach($clientResults as $res)
                                            <button type="button" wire:click="selectClient({{ $res->id }})"
                                                    class="w-full text-right p-3 hover:bg-indigo-50/80 dark:hover:bg-indigo-950/40 text-xs transition-colors group flex items-start gap-3">
                                                <span class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-bold flex items-center justify-center shrink-0 text-sm mt-0.5 group-hover:scale-105 transition-transform">
                                                    {{ mb_substr($res->full_name, 0, 1) }}
                                                </span>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span class="font-bold text-gray-900 dark:text-white text-xs truncate">{{ $res->full_name }}</span>
                                                        @if($res->case_number)
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                                پرونده: {{ $res->case_number }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                                        <span class="flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                                            {{ $res->phone ?: 'بدون تماس' }}
                                                        </span>
                                                        @if($res->national_code)
                                                            <span class="flex items-center gap-1">
                                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                                                                کد ملی: {{ $res->national_code }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- نام دستی و شماره پرونده --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نام کامل بیمار *</label>
                            <input type="text" wire:model="patientName" placeholder="نام و نام خانوادگی بیمار" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">شماره پرونده (دستی)</label>
                            <input type="text" wire:model="patientFileNumber" placeholder="مثلاً: 1403-882" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all">
                        </div>
                    </div>

                    {{-- نوع لابراتوار و نام مرکز --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نوع لابراتوار *</label>
                            <select wire:model.live="labType" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all">
                                @foreach($labTypes as $lt)
                                    <option value="{{ $lt['id'] }}">{{ $lt['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نام لابراتوار / مرکز همکار</label>
                            <input type="text" wire:model="labPartnerName" placeholder="مثلاً: آرمان سلامت، لابراتوار مطب..." class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all">
                        </div>
                    </div>

                    {{-- دسته‌بندی کار --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">دسته‌بندی سفارش *</label>
                            <select wire:model.live="categoryType" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all">
                                @forelse($availableCategories as $cat)
                                    <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                                @empty
                                    <option value="">دسته‌بندی تعریف نشده است</option>
                                @endforelse
                            </select>
                        </div>

                        @if($categoryType === 'full_jaw')
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">مرحله فول فک *</label>
                                <select wire:model="fullJawPhase" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all">
                                    <option value="base_rim">مرحله ۱: بیس ریم (۳، ۳، ۲ روز)</option>
                                    <option value="pmma">مرحله ۲: پی‌ام‌ای PMMA (۷، ۷، ۶ روز)</option>
                                    <option value="porcelain_try_in">مرحله ۳: تست پرسلن قبل گلیز (۷، ۷، ۶ روز)</option>
                                    <option value="final_glaze">مرحله ۴: ونیر / گلیز نهایی (۷، ۳، ۵ روز)</option>
                                </select>
                            </div>
                        @elseif($categoryType === 'units_7_11')
                            <div class="flex items-center gap-3 pt-6">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model.live="hasPmma" class="sr-only peer">
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600 dark:peer-checked:bg-indigo-500"></div>
                                </label>
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">نیازمند مرحله PMMA (افزودن ۱ مرحله به کار)</span>
                            </div>
                        @endif
                    </div>

                    {{-- بخش پیشرفته نقشه دندانی و انتخاب دندان‌ها --}}
                    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-4 space-y-3.5"
                         x-data="labToothPicker()">
                        
                        {{-- هدر نقشه دندانی با دکمه‌های سریع --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </span>
                                <div>
                                    <label class="block text-xs font-bold text-gray-900 dark:text-white">نقشه دندانی و شماره دندان‌ها</label>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">روی دندان‌ها در نقشه کلیک کنید یا از دکمه‌های زیر استفاده نمایید.</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 flex-wrap">
                                <button type="button" @click="selectJaw('upper')"
                                        :class="preset === 'upper' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700'"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-colors">فک بالا</button>
                                <button type="button" @click="selectJaw('lower')"
                                        :class="preset === 'lower' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700'"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-colors">فک پایین</button>
                                <button type="button" @click="selectAllTeeth()"
                                        :class="preset === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700'"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-colors">همه</button>
                                <button type="button" @click="resetTeeth()"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors">
                                    پاک‌سازی
                                </button>
                                <button type="button" @click="showChart = !showChart"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-medium text-gray-600 dark:text-gray-300 bg-gray-200/80 dark:bg-gray-700/80 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                                    <span x-text="showChart ? 'جمع کردن نقشه' : 'نمایش نقشه'"></span>
                                </button>
                            </div>
                        </div>

                        {{-- بدنه گرافیکی چارت دندان --}}
                        <div x-show="showChart" x-collapse class="relative bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700">
                            <div class="max-w-md mx-auto">
                                <x-booking::dental-chart />
                            </div>
                        </div>

                        {{-- پنل پالمر و خلاصه دندان‌های انتخاب شده --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-gray-600 dark:text-gray-300 shrink-0">دندان‌های انتخابی:</span>
                                
                                <template x-if="teeth.length > 0">
                                    <div class="inline-grid grid-cols-2 select-none shrink-0 align-middle">
                                        <!-- UR -->
                                        <div class="border-l-2 border-b-2 border-slate-300 dark:border-slate-500 pb-0.5 pl-2 flex items-center justify-end gap-1 min-w-[28px] min-h-[20px]">
                                            <template x-for="t in getQuadrantTeeth(teeth, 'UR')" :key="t">
                                                <span role="button" @click="toggle(t)"
                                                      class="inline-flex items-center justify-center min-w-[14px] text-xs font-black text-indigo-600 dark:text-indigo-400 hover:text-rose-500 transition-colors cursor-pointer"
                                                      :title="'حذف دندان ' + getToothLabel(t).num"
                                                      x-text="getToothLabel(t).num"></span>
                                            </template>
                                        </div>
                                        <!-- UL -->
                                        <div class="border-b-2 border-slate-300 dark:border-slate-500 pb-0.5 pr-2 flex items-center justify-start gap-1 min-w-[28px] min-h-[20px]">
                                            <template x-for="t in getQuadrantTeeth(teeth, 'UL')" :key="t">
                                                <span role="button" @click="toggle(t)"
                                                      class="inline-flex items-center justify-center min-w-[14px] text-xs font-black text-indigo-600 dark:text-indigo-400 hover:text-rose-500 transition-colors cursor-pointer"
                                                      :title="'حذف دندان ' + getToothLabel(t).num"
                                                      x-text="getToothLabel(t).num"></span>
                                            </template>
                                        </div>
                                        <!-- LR -->
                                        <div class="border-l-2 border-slate-300 dark:border-slate-500 pt-0.5 pl-2 flex items-center justify-end gap-1 min-w-[28px] min-h-[20px]">
                                            <template x-for="t in getQuadrantTeeth(teeth, 'LR')" :key="t">
                                                <span role="button" @click="toggle(t)"
                                                      class="inline-flex items-center justify-center min-w-[14px] text-xs font-black text-indigo-600 dark:text-indigo-400 hover:text-rose-500 transition-colors cursor-pointer"
                                                      :title="'حذف دندان ' + getToothLabel(t).num"
                                                      x-text="getToothLabel(t).num"></span>
                                            </template>
                                        </div>
                                        <!-- LL -->
                                        <div class="pt-0.5 pr-2 flex items-center justify-start gap-1 min-w-[28px] min-h-[20px]">
                                            <template x-for="t in getQuadrantTeeth(teeth, 'LL')" :key="t">
                                                <span role="button" @click="toggle(t)"
                                                      class="inline-flex items-center justify-center min-w-[14px] text-xs font-black text-indigo-600 dark:text-indigo-400 hover:text-rose-500 transition-colors cursor-pointer"
                                                      :title="'حذف دندان ' + getToothLabel(t).num"
                                                      x-text="getToothLabel(t).num"></span>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="teeth.length === 0">
                                    <span class="text-xs text-gray-400 dark:text-gray-500">هیچ دندانی انتخاب نشده است (روی نقشه کلیک کنید).</span>
                                </template>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/40"
                                      x-text="teeth.length + ' دندان انتخاب شده'"></span>
                            </div>
                        </div>

                        {{-- ردیف فیلدهای دستی تعداد واحد و تاریخ ارسال --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تعداد واحد (محاسبه خودکار با نقشه)</label>
                                <input type="number" wire:model="unitsCount" min="1" max="32" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تاریخ ارسال / اسکن (شمسی) *</label>
                                <input type="text" wire:model="sentAtJalali" placeholder="1403/07/03" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 transition-all">
                            </div>
                        </div>
                    </div>

                    {{-- توضیحات و یادداشت --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">توضیحات و دستور ساخت</label>
                        <textarea wire:model="orderNotes" rows="2" placeholder="رنگ دندان، متریال، نکات آناتومیک..." class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3.5 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all"></textarea>
                    </div>
                </div>

                <div class="p-5 border-t border-gray-100 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/50 flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('showCreateModal', false)" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors">انصراف</button>
                    <button type="button" wire:click="saveOrder" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all">
                        {{ $editingOrderId ? 'ذخیره تغییرات سفارش' : 'ثبت سفارش و محاسبه مراحل' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- مودال تایید / تکمیل سریع یک مرحله --}}
    @if($showStageModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl w-full max-w-md p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    تایید و تکمیل مرحله پیگیری
                </h3>

                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                    با تایید این مرحله، وضعیت آن به حالت «تکمیل شده» تغییر کرده و زمان پیگیری ثبت می‌شود. در صورت نیاز توضیحی اضافه نمایید:
                </p>

                <textarea wire:model="stageNote" rows="3" placeholder="توضیحات اختیاری (مثلاً: تماس گرفته شد، کار طبق برنامه پیش می‌رود)..."
                          class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all"></textarea>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" wire:click="$set('showStageModal', false)" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors">انصراف</button>
                    <button type="button" wire:click="confirmStageCompletion" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all">تایید و تکمیل مرحله</button>
                </div>
            </div>
        </div>
    @endif

    {{-- مودال دریافت نهایی کار (سبز شدن ردیف) --}}
    @if($showReceiveModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-emerald-300 dark:border-emerald-700/60 shadow-2xl w-full max-w-md p-6 space-y-4">
                <div class="flex items-center gap-3 text-emerald-600 dark:text-emerald-400">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">دریافت نهایی کار از لابراتوار</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">تغییر وضعیت ردیف به رنگ سبز</p>
                    </div>
                </div>

                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                    با تایید دریافت کار، کل مراحل سفارش تکمیل شده و ردیف مربوطه به رنگ <strong class="text-emerald-600 dark:text-emerald-400 font-bold">سبز</strong> تغییر می‌کند (نشانگر اینکه پروتز در مطب آماده تحویل است).
                </p>

                <textarea wire:model="receiveNote" rows="2" placeholder="توضیحات دریافت (اختیاری)..."
                          class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-emerald-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-emerald-500/20 transition-all"></textarea>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" wire:click="$set('showReceiveModal', false)" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors">انصراف</button>
                    <button type="button" wire:click="confirmReceiveOrder" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-500/20 transition-all">تایید دریافت کار (سبز)</button>
                </div>
            </div>
        </div>
    @endif

    {{-- مودال / کشوی جامع جزئیات کامل سفارش لابراتوار --}}
    @if($showDetailModal && $detailOrder)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl w-full max-w-4xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                
                {{-- هدر مودال جزئیات --}}
                <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/60 flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/25 shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                    جزئیات سفارش لابراتوار: {{ $detailOrder->order_number }}
                                </h3>
                                @if($detailOrder->isReceived())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        دریافت شده در مطب
                                    </span>
                                @elseif($detailOrder->isOverdue())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 dark:border-rose-700 animate-pulse">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        دارای تاخیر
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-300 dark:border-indigo-700">
                                        در جریان ساخت
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                ثبت شده در تاریخ {{ $detailOrder->created_at_jalali ?: '-' }} 
                                @if($detailOrder->createdBy)
                                    توسط {{ $detailOrder->createdBy->name }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" 
                                wire:click="closeDetailModal" 
                                class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                {{-- تب‌های ناوبری بالای مودال جزئیات --}}
                <div class="px-5 sm:px-6 pt-3 pb-0 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex items-center gap-2 overflow-x-auto">
                    <button type="button" 
                            wire:click="setDetailTab('overview')"
                            class="pb-3 px-3.5 text-xs font-bold transition-all border-b-2 flex items-center gap-1.5 whitespace-nowrap {{ $detailModalTab === 'overview' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <span>خلاصه و مراحل ساخت</span>
                    </button>

                    <button type="button" 
                            wire:click="setDetailTab('dental_chart')"
                            class="pb-3 px-3.5 text-xs font-bold transition-all border-b-2 flex items-center gap-1.5 whitespace-nowrap {{ $detailModalTab === 'dental_chart' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        <span>نقشه دندانی و واحدها</span>
                        @if($detailOrder->units_count > 0)
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">{{ $detailOrder->units_count }}</span>
                        @endif
                    </button>

                    <button type="button" 
                            wire:click="setDetailTab('logs')"
                            class="pb-3 px-3.5 text-xs font-bold transition-all border-b-2 flex items-center gap-1.5 whitespace-nowrap {{ $detailModalTab === 'logs' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>لاگ‌های پیگیری روزانه</span>
                        @if($detailOrder->dailyLogs && $detailOrder->dailyLogs->count() > 0)
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200">{{ $detailOrder->dailyLogs->count() }}</span>
                        @endif
                    </button>
                </div>

                {{-- محتوای اسکرول‌پذیر مودال جزئیات --}}
                <div class="p-5 sm:p-6 space-y-6 max-h-[75vh] overflow-y-auto text-xs">
                    
                    {{-- تب ۱: خلاصه و مراحل ساخت --}}
                    @if($detailModalTab === 'overview')
                        {{-- اطلاعات بیمار، پزشک و طرفین --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                            {{-- بیمار --}}
                            <div class="p-3.5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/80 space-y-1">
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 block">بیمار</span>
                                <div class="font-bold text-sm text-gray-900 dark:text-white">{{ $detailOrder->patient_name }}</div>
                                @if($detailOrder->patient_file_number)
                                    <div class="text-[11px] text-slate-600 dark:text-slate-300">شماره پرونده: {{ $detailOrder->patient_file_number }}</div>
                                @endif
                                @if($detailOrder->client?->phone)
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">تماس: {{ $detailOrder->client->phone }}</div>
                                @endif
                            </div>

                            {{-- پزشک معالج --}}
                            <div class="p-3.5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/80 space-y-1">
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 block">پزشک معالج</span>
                                <div class="font-bold text-sm text-gray-900 dark:text-white">{{ $detailOrder->doctor?->name ?: 'تعیین نشده' }}</div>
                                @if($detailOrder->technician)
                                    <div class="text-[11px] text-slate-600 dark:text-slate-300">تکنسین: {{ $detailOrder->technician->name }}</div>
                                @endif
                            </div>

                            {{-- لابراتوار و تاریخ ارسال --}}
                            <div class="p-3.5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/80 space-y-1">
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 block">لابراتوار مجری</span>
                                <div class="font-bold text-sm text-indigo-600 dark:text-indigo-400">{{ $detailOrder->lab_partner_name }}</div>
                                <div class="text-[11px] text-slate-600 dark:text-slate-300">تاریخ ارسال: {{ $detailOrder->sent_at_jalali ?: '-' }}</div>
                            </div>

                            {{-- موعد و زمان تحویل --}}
                            <div class="p-3.5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/80 space-y-1">
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 block">موعد تحویل نهایی</span>
                                <div class="font-bold text-sm text-gray-900 dark:text-white">{{ $detailOrder->expected_delivery_at_jalali ?: 'تعیین نشده' }}</div>
                                @if($detailOrder->received_at_jalali)
                                    <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold">تحویل مطب: {{ $detailOrder->received_at_jalali }}</div>
                                @endif
                            </div>
                        </div>

                        {{-- خلاصه مشخصات پروتز با لینک به تب نقشه دندانی --}}
                        <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 shadow-xs flex items-center justify-between gap-4 flex-wrap">
                            <div class="flex items-center gap-3 flex-wrap">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold">
                                    <span>نوع کار:</span>
                                    <span class="text-indigo-600 dark:text-indigo-400">{{ $detailOrder->category_label }}</span>
                                </div>

                                @if($detailOrder->units_count > 0)
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200 dark:border-indigo-800">
                                        <span>تعداد واحد:</span>
                                        <span>{{ $detailOrder->units_count }} واحد</span>
                                    </div>
                                @endif

                                @if($detailOrder->has_pmma)
                                    <div class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-bold border border-amber-200 dark:border-amber-800">
                                        دارای مرحله PMMA
                                    </div>
                                @endif
                            </div>

                            <button type="button" 
                                    wire:click="setDetailTab('dental_chart')"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-bold text-xs border border-indigo-200 dark:border-indigo-800/60 transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                مشاهده نقشه گرافیکی دندان‌ها ↵
                            </button>
                        </div>

                        {{-- تایم‌لاین مراحل ساخت و زمان‌بندی پیگیری‌ها --}}
                        <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 shadow-xs space-y-4">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <h4 class="font-bold text-xs text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    زمان‌بندی و مراحل پیگیری ساخت
                                </h4>

                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-slate-500 dark:text-slate-400">پیشرفت کل:</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $detailOrder->completed_stages_count }} از {{ $detailOrder->total_stages_count }} مرحله ({{ $detailOrder->progress_percentage }}٪)</span>
                                </div>
                            </div>

                            {{-- نوار پیشرفت سراسری --}}
                            <div class="w-full bg-slate-100 dark:bg-slate-700/60 rounded-full h-2 overflow-hidden">
                                <div class="bg-indigo-600 dark:bg-indigo-500 h-2 rounded-full transition-all duration-300" style="width: {{ $detailOrder->progress_percentage }}%"></div>
                            </div>

                            {{-- لیست تایم‌لاین عمودی مراحل --}}
                            <div class="relative pl-2 pr-4 space-y-4 before:absolute before:right-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-700">
                                @forelse($detailOrder->stages as $stIndex => $st)
                                    @php
                                        $isDone = $st->is_completed;
                                        $isOver = $st->isOverdue();
                                        $isToday = $st->isDueToday();
                                    @endphp
                                    <div class="relative flex items-start gap-3.5 group">
                                        {{-- نقطه وضعیت در خط زمان --}}
                                        <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 z-10 
                                            {{ $isDone ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-500/30' : ($isOver ? 'bg-rose-600 text-white shadow-sm shadow-rose-500/30 animate-pulse' : ($isToday ? 'bg-amber-500 text-white' : 'bg-slate-300 dark:bg-slate-600 text-slate-700 dark:text-slate-200')) }}">
                                            @if($isDone)
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                            @else
                                                <span class="text-[10px] font-bold">{{ $stIndex + 1 }}</span>
                                            @endif
                                        </div>

                                        {{-- کارت جزئیات مرحله --}}
                                        <div class="flex-1 p-3 rounded-xl border {{ $isDone ? 'bg-emerald-50/40 border-emerald-200/80 dark:bg-emerald-950/20 dark:border-emerald-800/40' : ($isOver ? 'bg-rose-50/40 border-rose-200/80 dark:bg-rose-950/20 dark:border-rose-800/40' : ($isToday ? 'bg-amber-50/40 border-amber-200/80 dark:bg-amber-950/20 dark:border-amber-800/40' : 'bg-gray-50/70 border-gray-200/80 dark:bg-gray-900/30 dark:border-gray-700/80')) }} flex items-center justify-between gap-3 flex-wrap">
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <h5 class="font-bold text-xs text-gray-900 dark:text-white">{{ $st->stage_title }}</h5>
                                                    @if($isDone)
                                                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400">✓ تکمیل شده</span>
                                                    @elseif($isOver)
                                                        <span class="text-[10px] font-bold text-rose-700 dark:text-rose-400">! دارای تاخیر</span>
                                                    @elseif($isToday)
                                                        <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400">● موعد امروز</span>
                                                    @else
                                                        <span class="text-[10px] text-slate-500 dark:text-slate-400">در انتظار</span>
                                                    @endif
                                                </div>
                                                <div class="flex flex-wrap items-center gap-x-4 text-[11px] text-slate-600 dark:text-slate-300">
                                                    <span>موعد برنامه‌ریزی: <strong>{{ $st->due_at_jalali }}</strong></span>
                                                    @if($st->completed_at_jalali)
                                                        <span class="text-emerald-700 dark:text-emerald-400">تاریخ تایید: {{ $st->completed_at_jalali }}</span>
                                                    @endif
                                                    @if($st->completedBy)
                                                        <span>توسط: {{ $st->completedBy->name }}</span>
                                                    @endif
                                                </div>
                                                @if($st->notes)
                                                    <div class="text-[11px] text-slate-600 dark:text-slate-400 italic bg-white/70 dark:bg-slate-800/60 p-1.5 rounded-lg mt-1 border border-slate-200/60 dark:border-slate-700/60">
                                                        توضیحات: {{ $st->notes }}
                                                    </div>
                                                @endif
                                            </div>

                                            @if(!$isDone)
                                                <button type="button" 
                                                        wire:click="openStageModal({{ $st->id }})"
                                                        class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm shadow-indigo-500/20 transition-all shrink-0">
                                                    تایید و ثبت انجام
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-slate-400 dark:text-slate-500 text-xs py-2">هیچ مرحله‌ای برای این سفارش تعریف نشده است.</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- دستور ساخت بالینی و یادداشت‌های پزشک --}}
                        @if($detailOrder->notes)
                            <div class="p-4 rounded-2xl bg-amber-50/40 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-800/40 space-y-1.5">
                                <h4 class="font-bold text-xs text-amber-900 dark:text-amber-200 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
                                    دستور ساخت و یادداشت‌های بالینی
                                </h4>
                                <p class="text-xs text-amber-900/80 dark:text-amber-200/80 leading-relaxed whitespace-pre-line">
                                    {{ $detailOrder->notes }}
                                </p>
                            </div>
                        @endif

                    {{-- تب ۲: نقشه دندانی و واحدها --}}
                    @elseif($detailModalTab === 'dental_chart')
                        <div x-data="labDetailToothViewer('{{ $detailOrder->teeth_numbers }}')" class="space-y-6">
                            
                            {{-- کارت نقشه دندانی گرافیکی با هایلایت دندان‌ها --}}
                            <div class="p-5 rounded-3xl bg-slate-50/80 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/80 shadow-xs space-y-4">
                                <div class="flex items-center justify-between gap-4 flex-wrap border-b border-slate-200/80 dark:border-slate-700/80 pb-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                            🦷
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">نقشه دندانی گرافیکی سفارش (Dental Anatomy Chart)</h4>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">دندان‌های مشخص شده برای این سفارش با رنگ شاخص نیلی هایلایت شده‌اند</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                            مجموع: <span x-text="teeth.length"></span> واحد دندانی
                                        </span>
                                    </div>
                                </div>

                                {{-- کامپوننت چارت دندانی --}}
                                <div class="max-w-2xl mx-auto py-2">
                                    <x-booking::dental-chart />
                                </div>
                            </div>

                            {{-- کارت‌های تفکیک چهار چارک پالمر و FDI --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- فک بالا راست (UR) --}}
                                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 space-y-2.5">
                                    <div class="flex items-center justify-between border-b border-gray-150 dark:border-gray-700 pb-2">
                                        <h5 class="font-bold text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                            فک بالا - سمت راست (UR ⏌)
                                        </h5>
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400" x-text="getQuadrantTeeth('UR').length + ' دندان'"></span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 min-h-[36px] items-center">
                                        <template x-for="t in getQuadrantTeeth('UR')" :key="t.id">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                                <span class="font-black text-sm" x-text="'⏌' + t.num"></span>
                                                <span class="text-[10px] opacity-75" x-text="'(FDI: ' + t.fdi + ')'"></span>
                                                <span class="text-[10px] font-normal" x-text="t.name"></span>
                                            </span>
                                        </template>
                                        <template x-if="getQuadrantTeeth('UR').length === 0">
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500 italic">دندانی در این بخش انتخاب نشده است.</span>
                                        </template>
                                    </div>
                                </div>

                                {{-- فک بالا چپ (UL) --}}
                                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 space-y-2.5">
                                    <div class="flex items-center justify-between border-b border-gray-150 dark:border-gray-700 pb-2">
                                        <h5 class="font-bold text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                            فک بالا - سمت چپ (UL ⎾)
                                        </h5>
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400" x-text="getQuadrantTeeth('UL').length + ' دندان'"></span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 min-h-[36px] items-center">
                                        <template x-for="t in getQuadrantTeeth('UL')" :key="t.id">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                                <span class="font-black text-sm" x-text="t.num + '⎾'"></span>
                                                <span class="text-[10px] opacity-75" x-text="'(FDI: ' + t.fdi + ')'"></span>
                                                <span class="text-[10px] font-normal" x-text="t.name"></span>
                                            </span>
                                        </template>
                                        <template x-if="getQuadrantTeeth('UL').length === 0">
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500 italic">دندانی در این بخش انتخاب نشده است.</span>
                                        </template>
                                    </div>
                                </div>

                                {{-- فک پایین راست (LR) --}}
                                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 space-y-2.5">
                                    <div class="flex items-center justify-between border-b border-gray-150 dark:border-gray-700 pb-2">
                                        <h5 class="font-bold text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                            فک پایین - سمت راست (LR ⏋)
                                        </h5>
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400" x-text="getQuadrantTeeth('LR').length + ' دندان'"></span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 min-h-[36px] items-center">
                                        <template x-for="t in getQuadrantTeeth('LR')" :key="t.id">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                                <span class="font-black text-sm" x-text="'⏋' + t.num"></span>
                                                <span class="text-[10px] opacity-75" x-text="'(FDI: ' + t.fdi + ')'"></span>
                                                <span class="text-[10px] font-normal" x-text="t.name"></span>
                                            </span>
                                        </template>
                                        <template x-if="getQuadrantTeeth('LR').length === 0">
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500 italic">دندانی در این بخش انتخاب نشده است.</span>
                                        </template>
                                    </div>
                                </div>

                                {{-- فک پایین چپ (LL) --}}
                                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 space-y-2.5">
                                    <div class="flex items-center justify-between border-b border-gray-150 dark:border-gray-700 pb-2">
                                        <h5 class="font-bold text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                            فک پایین - سمت چپ (LL ⎿)
                                        </h5>
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400" x-text="getQuadrantTeeth('LL').length + ' دندان'"></span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 min-h-[36px] items-center">
                                        <template x-for="t in getQuadrantTeeth('LL')" :key="t.id">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                                <span class="font-black text-sm" x-text="t.num + '⎿'"></span>
                                                <span class="text-[10px] opacity-75" x-text="'(FDI: ' + t.fdi + ')'"></span>
                                                <span class="text-[10px] font-normal" x-text="t.name"></span>
                                            </span>
                                        </template>
                                        <template x-if="getQuadrantTeeth('LL').length === 0">
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500 italic">دندانی در این بخش انتخاب نشده است.</span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                    {{-- تب ۳: لاگ‌های پیگیری روزانه --}}
                    @elseif($detailModalTab === 'logs')
                        <div class="p-5 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 shadow-xs space-y-4">
                            <div class="flex items-center justify-between border-b border-gray-150 dark:border-gray-700 pb-3">
                                <h4 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                    تاریخچه کامل پیگیری‌ها و تماس‌های ثبت‌شده با لابراتوار
                                </h4>
                            </div>

                            @if($detailOrder->dailyLogs && $detailOrder->dailyLogs->count() > 0)
                                <div class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                    @foreach($detailOrder->dailyLogs as $log)
                                        <div class="py-3 flex items-start justify-between gap-4 text-xs">
                                            <div class="space-y-1">
                                                <div class="font-bold text-gray-900 dark:text-white text-sm">{{ $log->followup_result }}</div>
                                                <div class="flex items-center gap-3 text-[11px] text-slate-500 dark:text-slate-400">
                                                    <span>ثبت شده توسط: <strong>{{ $log->operator?->name ?: 'سیستم' }}</strong></span>
                                                    <span>•</span>
                                                    <span>تاریخ: <strong>{{ $log->log_date_jalali ?: '-' }}</strong></span>
                                                    @if($log->stage)
                                                        <span>•</span>
                                                        <span>مرحله: <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $log->stage->stage_title }}</span></span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($log->needs_followup)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800/40 shrink-0">
                                                    نیازمند پیگیری مجدد
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40 shrink-0">
                                                    پاسخ قطعی / تکمیل
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-slate-400 dark:text-slate-500 italic py-4 text-center">هنوز هیچ گزارش تماسی برای این سفارش در کارتابل پیگیری روزانه ثبت نشده است.</p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- فوتر مودال جزئیات --}}
                <div class="p-4 sm:p-5 border-t border-gray-100 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/50 flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                wire:click="closeDetailModal; openEditModal({{ $detailOrder->id }})"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-xs font-bold transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            ویرایش مشخصات سفارش
                        </button>

                        @if(!$detailOrder->isReceived())
                            <button type="button" 
                                    wire:click="closeDetailModal; openReceiveModal({{ $detailOrder->id }})"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-500/20 transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                دریافت نهایی کار (سبز شدن)
                            </button>
                        @endif
                    </div>

                    <button type="button" 
                            wire:click="closeDetailModal" 
                            class="px-5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors">
                        بستن
                    </button>
                </div>

            </div>
        </div>
    @endif

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('labToothPicker', () => ({
                teeth: [],
                preset: 'none',
                showChart: true,

                init() {
                    this.loadFromLivewire();
                    this.$watch('$wire.teethNumbers', (val) => {
                        this.loadFromLivewire();
                    });
                },

                loadFromLivewire() {
                    const val = this.$wire.get('teethNumbers');
                    if (!val) {
                        this.teeth = [];
                        return;
                    }
                    const parts = String(val).split(/[,\s\-]+/).map(s => parseInt(s.trim())).filter(n => !isNaN(n) && n >= 1 && n <= 28);
                    this.teeth = [...new Set(parts)].sort((a, b) => a - b);
                },

                getToothLabel(id) {
                    const mapping = {
                        1:  { num: 7, pos: 'UR' }, 2:  { num: 6, pos: 'UR' }, 3:  { num: 5, pos: 'UR' }, 4:  { num: 4, pos: 'UR' },
                        5:  { num: 3, pos: 'UR' }, 6:  { num: 2, pos: 'UR' }, 7:  { num: 1, pos: 'UR' },
                        8:  { num: 1, pos: 'UL' }, 9:  { num: 2, pos: 'UL' }, 10: { num: 3, pos: 'UL' }, 11: { num: 4, pos: 'UL' },
                        12: { num: 5, pos: 'UL' }, 13: { num: 6, pos: 'UL' }, 14: { num: 7, pos: 'UL' },
                        15: { num: 7, pos: 'LR' }, 16: { num: 6, pos: 'LR' }, 17: { num: 5, pos: 'LR' }, 18: { num: 4, pos: 'LR' },
                        19: { num: 3, pos: 'LR' }, 20: { num: 2, pos: 'LR' }, 21: { num: 1, pos: 'LR' },
                        22: { num: 1, pos: 'LL' }, 23: { num: 2, pos: 'LL' }, 24: { num: 3, pos: 'LL' }, 25: { num: 4, pos: 'LL' },
                        26: { num: 5, pos: 'LL' }, 27: { num: 6, pos: 'LL' }, 28: { num: 7, pos: 'LL' }
                    };
                    return mapping[id] ?? { num: id, pos: 'UR' };
                },

                getQuadrantTeeth(teethArray, pos) {
                    return (teethArray || []).map(Number).filter(t => this.getToothLabel(t).pos === pos).sort((a,b) => {
                        const lA = this.getToothLabel(a).num;
                        const lB = this.getToothLabel(b).num;
                        if (pos === 'UR' || pos === 'LR') return lB - lA;
                        return lA - lB;
                    });
                },

                toggle(id) {
                    id = Number(id);
                    if (!Array.isArray(this.teeth)) this.teeth = [];
                    const idx = this.teeth.indexOf(id);
                    if (idx > -1) {
                        this.teeth.splice(idx, 1);
                    } else {
                        this.teeth.push(id);
                    }
                    this.teeth.sort((a, b) => a - b);
                    this.preset = 'none';
                    this.syncToLivewire();
                },

                is(id) {
                    id = Number(id);
                    return (Array.isArray(this.teeth) && this.teeth.includes(id)) ? 'tooth-path tooth-selected' : 'tooth-path tooth-unselected';
                },

                selectJaw(jaw) {
                    if (jaw === 'upper') {
                        this.teeth = Array.from({length: 14}, (_, i) => i + 1);
                        this.preset = 'upper';
                    } else if (jaw === 'lower') {
                        this.teeth = Array.from({length: 14}, (_, i) => i + 15);
                        this.preset = 'lower';
                    }
                    this.syncToLivewire();
                },

                selectAllTeeth() {
                    this.teeth = Array.from({length: 28}, (_, i) => i + 1);
                    this.preset = 'all';
                    this.syncToLivewire();
                },

                resetTeeth() {
                    this.teeth = [];
                    this.preset = 'none';
                    this.syncToLivewire();
                },

                syncToLivewire() {
                    const str = this.teeth.join(', ');
                    this.$wire.set('teethNumbers', str);
                    if (this.teeth.length > 0) {
                        this.$wire.set('unitsCount', this.teeth.length);
                    }
                }
            }));

            Alpine.data('labDetailToothViewer', (rawTeethStr) => ({
                teeth: [],

                init() {
                    if (!rawTeethStr) {
                        this.teeth = [];
                        return;
                    }
                    const parts = String(rawTeethStr).split(/[,\s\-]+/).map(s => parseInt(s.trim())).filter(n => !isNaN(n) && n >= 1 && n <= 28);
                    this.teeth = [...new Set(parts)].sort((a, b) => a - b);
                },

                getToothLabel(id) {
                    const mapping = {
                        1:  { num: 7, fdi: 17, pos: 'UR', name: 'مولر دوم بالا راست' },
                        2:  { num: 6, fdi: 16, pos: 'UR', name: 'مولر اول بالا راست' },
                        3:  { num: 5, fdi: 15, pos: 'UR', name: 'پری‌مولر دوم بالا راست' },
                        4:  { num: 4, fdi: 14, pos: 'UR', name: 'پری‌مولر اول بالا راست' },
                        5:  { num: 3, fdi: 13, pos: 'UR', name: 'کانین (نیش) بالا راست' },
                        6:  { num: 2, fdi: 12, pos: 'UR', name: 'لترال بالا راست' },
                        7:  { num: 1, fdi: 11, pos: 'UR', name: 'سنترال بالا راست' },
                        8:  { num: 1, fdi: 21, pos: 'UL', name: 'سنترال بالا چپ' },
                        9:  { num: 2, fdi: 22, pos: 'UL', name: 'لترال بالا چپ' },
                        10: { num: 3, fdi: 23, pos: 'UL', name: 'کانین (نیش) بالا چپ' },
                        11: { num: 4, fdi: 24, pos: 'UL', name: 'پری‌مولر اول بالا چپ' },
                        12: { num: 5, fdi: 25, pos: 'UL', name: 'پری‌مولر دوم بالا چپ' },
                        13: { num: 6, fdi: 26, pos: 'UL', name: 'مولر اول بالا چپ' },
                        14: { num: 7, fdi: 27, pos: 'UL', name: 'مولر دوم بالا چپ' },
                        15: { num: 7, fdi: 47, pos: 'LR', name: 'مولر دوم پایین راست' },
                        16: { num: 6, fdi: 46, pos: 'LR', name: 'مولر اول پایین راست' },
                        17: { num: 5, fdi: 45, pos: 'LR', name: 'پری‌مولر دوم پایین راست' },
                        18: { num: 4, fdi: 44, pos: 'LR', name: 'پری‌مولر اول پایین راست' },
                        19: { num: 3, fdi: 43, pos: 'LR', name: 'کانین (نیش) پایین راست' },
                        20: { num: 2, fdi: 42, pos: 'LR', name: 'لترال پایین راست' },
                        21: { num: 1, fdi: 41, pos: 'LR', name: 'سنترال پایین راست' },
                        22: { num: 1, fdi: 31, pos: 'LL', name: 'سنترال پایین چپ' },
                        23: { num: 2, fdi: 32, pos: 'LL', name: 'لترال پایین چپ' },
                        24: { num: 3, fdi: 33, pos: 'LL', name: 'کانین (نیش) پایین چپ' },
                        25: { num: 4, fdi: 34, pos: 'LL', name: 'پری‌مولر اول پایین چپ' },
                        26: { num: 5, fdi: 35, pos: 'LL', name: 'پری‌مولر دوم پایین چپ' },
                        27: { num: 6, fdi: 36, pos: 'LL', name: 'مولر اول پایین چپ' },
                        28: { num: 7, fdi: 37, pos: 'LL', name: 'مولر دوم پایین چپ' }
                    };
                    return mapping[id] ?? { num: id, fdi: id, pos: 'UR', name: 'دندان شماره ' + id };
                },

                getQuadrantTeeth(pos) {
                    return (this.teeth || []).map(Number).filter(t => this.getToothLabel(t).pos === pos).map(t => ({
                        id: t,
                        ...this.getToothLabel(t)
                    })).sort((a,b) => {
                        if (pos === 'UR' || pos === 'LR') return b.num - a.num;
                        return a.num - b.num;
                    });
                },

                toggle(id) {},

                is(id) {
                    id = Number(id);
                    return (Array.isArray(this.teeth) && this.teeth.includes(id)) ? 'tooth-path tooth-selected' : 'tooth-path tooth-unselected';
                }
            }));
        });
    </script>
</div>
