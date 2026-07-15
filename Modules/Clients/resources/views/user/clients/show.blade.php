@extends('layouts.user')

@php
    // عنوان صفحه
    $title = 'نمایش ' . config('clients.labels.singular');

    // وضعیت فعلی کلاینت (ممکنه null باشه)
    /** @var \Modules\Clients\Entities\Client $client */
    $statusObj   = optional($client->status);
    $statusLabel = $statusObj->label ?? 'بدون وضعیت';
    $statusKey   = $statusObj->key   ?? null;

    // کلاس‌های ظاهری بج برای وضعیت
    $statusBadgeClasses = match ($statusKey) {
        'new'        => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/30 dark:text-blue-200 dark:border-blue-800',
        'active'     => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-200 dark:border-emerald-800',
        'pending'    => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/30 dark:text-amber-200 dark:border-amber-800',
        'cancelled'  => 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/30 dark:text-red-200 dark:border-red-800',
        'blacklist'  => 'bg-gray-800 text-gray-100 border-gray-900 dark:bg-black dark:text-gray-100 dark:border-gray-900',
        default      => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600',
    };
    $clientCallsModule = \App\Models\Module::where('slug', 'clientcalls')->first();
    $followUpsModule   = \App\Models\Module::where('slug', 'followups')->first();
    $statusMap = [
        'planned' => [
            'label' => 'برنامه‌ریزی شده',
            'class' => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/40 dark:text-blue-200 dark:border-blue-700',
        ],
        'done' => [
            'label' => 'انجام شده',
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-200 dark:border-emerald-700',
        ],
        'failed' => [
            'label' => 'ناموفق',
            'class' => 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/40 dark:text-red-200 dark:border-red-700',
        ],
        'canceled' => [
            'label' => 'لغو شده',
            'class' => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700/60 dark:text-gray-200 dark:border-gray-600',
        ],
    ];
@endphp

@section('content')
    <div class="mx-auto max-w-full space-y-6">
        {{-- کارت اصلی --}}
        <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">

            {{-- هدر پروفایل --}}
            <div
                    class="relative bg-gray-50/50 dark:bg-gray-900/30 border-b border-gray-200 dark:border-gray-700 p-6 sm:p-8">
                <div class="flex flex-col-2 sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        {{-- آواتار حروف اول --}}
                        <div
                                class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300 text-2xl font-bold ring-4 ring-white dark:ring-gray-800">
                            {{ mb_substr($client->full_name, 0, 1) }}
                        </div>

                        <div>
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ $client->full_name }}
                            </h1>

                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs sm:text-sm">
                                {{-- یوزرنیم --}}
                                <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400 font-mono">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>{{ $client->username }}</span>
                                </div>

                                {{-- بج وضعیت --}}
                                <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-xs font-medium {{ $statusBadgeClasses }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current/40"></span>
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- دکمه‌ها --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('user.clients.index') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800 transition-all dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            <span>بازگشت</span>
                        </a>

                        @can('clients.edit')
                            <a href="{{ route('user.clients.edit', $client) }}"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>ویرایش</span>
                            </a>
                        @endcan

                        {{-- 🔹 دکمه ورود مستقیم به پروفایل مشتری در تب جدید --}}
                        @can('clients.edit')
                            <a href="{{ route('user.clients.portal-login', $client) }}"
                               target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-500/30 transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span>ورود به پنل {{config('clients.labels.singular')}}</span>
                            </a>
                        @endcan

                        {{-- در view پروفایل کلاینت، یک دکمه کنار هدر --}}
                        @if($clientCallsModule && $clientCallsModule->installed && $clientCallsModule->active)
                            @can('client-calls.view')
                                <a href="{{ route('user.clients.calls.index', $client) }}"
                                   class="inline-flex items-center gap-1 px-4 py-2 rounded-xl bg-sky-600 text-white text-sm font-medium hover:bg-sky-700 hover:shadow-lg hover:shadow-sky-500/30 transition-all dark:bg-sky-700 dark:text-sky-200 dark:border-sky-700 dark:hover:bg-sky-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <span>تاریخچه تماس‌ها</span>
                                </a>
                            @endcan
                        @endif

                        @if($followUpsModule && $followUpsModule->installed && $followUpsModule->active)
                            @can('followups.create')
                                {{-- ایجاد پیگیری برای این کلاینت (می‌رود به فرم عمومی پیگیری‌ها، اگر بخواهی) --}}
                                <a href="{{ route('user.followups.create', [
                    'related_type' => \Modules\Tasks\Entities\Task::RELATED_TYPE_CLIENT,
                    'related_id'   => $client->id,
               ]) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500 text-white text-sm font-medium hover:bg-amber-600 hover:shadow-lg hover:shadow-amber-500/30 transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>ایجاد پیگیری</span>
                                </a>
                            @endcan

                            @can('followups.view')
                                {{-- تاریخچه کامل پیگیری‌های مرتبط با این کلاینت --}}
                                <a href="{{ route('user.followups.index', [
                    'related_type' => \Modules\Tasks\Entities\Task::RELATED_TYPE_CLIENT,
                    'related_id'   => $client->id,
               ]) }}"
                                   class="inline-flex items-center gap-1 px-4 py-2 rounded-xl bg-amber-600 text-white text-sm font-medium hover:bg-amber-700 hover:shadow-lg hover:shadow-amber-500/30 transition-all dark:bg-amber-700 dark:text-amber-100">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <span>تاریخچه پیگیری‌ها</span>
                                </a>
                            @endcan
                        @endif


                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8 grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- ستون اطلاعات پایه --}}
                <div class="lg:col-span-2 space-y-8">

                    {{-- بخش تماس --}}
                    <section>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            اطلاعات تماس
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div
                                    class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">ایمیل</div>
                                <div
                                        class="font-medium text-gray-900 dark:text-gray-200 dir-ltr break-all flex items-center gap-2">
                                    @if($client->email)
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $client->email }}
                                    @else
                                        <span class="text-gray-400 italic">—</span>
                                    @endif
                                </div>
                            </div>

                            <div
                                    class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">تلفن تماس</div>
                                <div
                                        class="font-medium text-gray-900 dark:text-gray-200 dir-ltr text-right flex items-center justify-end gap-2">
                                    @if($client->phone)
                                        {{ $client->phone }}
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                    @else
                                        <span class="text-gray-400 italic">—</span>
                                    @endif
                                </div>
                            </div>

                            {{-- کد ملی --}}
                            @if($client->national_code)
                                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">کد ملی</div>
                                    <div class="font-medium text-gray-900 dark:text-gray-200 dir-ltr text-right flex items-center justify-end gap-2">
                                        {{ $client->national_code }}
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884-.5 2-2 2h4c-1.5 0-2-1.116-2-2z" />
                                        </svg>
                                    </div>
                                </div>
                            @endif

                            {{-- شماره پرونده --}}
                            @if($client->case_number)
                                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">شماره پرونده</div>
                                    <div class="font-medium text-gray-900 dark:text-gray-200 dir-ltr text-right flex items-center justify-end gap-2">
                                        {{ $client->case_number }}
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>

                    {{-- بخش یادداشت --}}
                    @if($client->notes)
                        <section>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>
                                یادداشت‌ها
                            </h3>
                            <div
                                    class="p-4 rounded-xl bg-yellow-50 border border-yellow-100 text-yellow-900 dark:bg-yellow-900/20 dark:border-yellow-900/30 dark:text-yellow-200 text-sm leading-relaxed whitespace-pre-wrap">
                                {{ $client->notes }}
                            </div>
                        </section>
                    @endif
                    @if($clientCallsModule && $clientCallsModule->installed && $clientCallsModule->active)
                        @can('client-calls.view')
                            @if($client->calls->count())
                                <div @click="open = ! open" x-data="{ open: true }" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                                    <h3 class="text-sm font-semibold cursor-pointer text-gray-900 dark:text-white mb-0 flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                        تاریخچه سریع تماس‌ها
                                    </h3>

                                    <div x-show="open" x-collapse class="space-y-3 mt-4">
                                            @foreach($client->calls->sortByDesc('call_date')->take(3) as $call)
                                                @php
                                                    $statusKey = $call->status ?? 'unknown';
                                                    $statusInfo = $statusMap[$statusKey] ?? [
                                                        'label' => 'نامشخص',
                                                        'class' => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700/60 dark:text-gray-200 dark:border-gray-600',
                                                    ];
                                                    $dateText = $call->call_date ? \Morilog\Jalali\Jalalian::fromCarbon($call->call_date)->format('Y/m/d') : '—';
                                                    $timeText = $call->call_time ? \Carbon\Carbon::parse($call->call_time)->format('H:i') : '—';
                                                @endphp

                                                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                                                    <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full border text-xs {{ $statusInfo['class'] }}">
                                {{ $statusInfo['label'] }}
                            </span>

                                                        <span class="text-sm text-gray-500 dark:text-gray-400 dir-ltr">
                                {{ $dateText }} - {{ $timeText }}
                            </span>
                                                    </div>

                                                    @if($call->reason)
                                                        <div class="text-sm text-gray-700 dark:text-gray-300">
                                                            <span class="font-semibold">علت:</span> {{ $call->reason }}
                                                        </div>
                                                    @endif

                                                    @if($call->result)
                                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-2 line-clamp-2">
                                                            <span class="font-semibold">نتیجه:</span> {{ $call->result }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                </div>
                            @endif

                        @endcan
                    @endif
                    @if($followUpsModule && $followUpsModule->installed && $followUpsModule->active)
                        @can('followups.view')
                            @php
                                $followUpsQuick      = $client->followUps->take(3);
                                $taskStatusOptions   = \Modules\Tasks\Entities\Task::statusOptions();
                                $taskPriorityOptions = \Modules\Tasks\Entities\Task::priorityOptions();
                            @endphp

                            @if($followUpsQuick->count())
                                <div x-data="{ openFU: true }"
                                     class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                                    <div class="flex items-center justify-between cursor-pointer mb-0" @click="openFU = !openFU">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-0 flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            تاریخچه سریع پیگیری‌ها
                                        </h3>

                                        <div class="flex items-center gap-2 text-xs">
                        <span class="text-gray-500 dark:text-gray-400">
                            {{ $followUpsQuick->count() }} مورد اخیر
                        </span>
                                            @can('followups.view')
                                                <a href="{{ route('user.followups.index', [
                                    'related_type' => \Modules\Tasks\Entities\Task::RELATED_TYPE_CLIENT,
                                    'related_id'   => $client->id,
                                ]) }}"
                                                   class="inline-flex items-center gap-1 text-amber-600 hover:text-amber-700 dark:text-amber-300 dark:hover:text-amber-100">
                                                    <span>مشاهده همه</span>
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                                              d="M13 5h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                            @endcan
                                        </div>
                                    </div>

                                    <div x-show="openFU" x-collapse class="space-y-3 mt-4">
                                        @foreach($followUpsQuick as $fu)
                                            @php
                                                $statusLabel   = $taskStatusOptions[$fu->status] ?? $fu->status;
                                                $priorityLabel = $taskPriorityOptions[$fu->priority] ?? $fu->priority;
                                                $dueText       = $fu->due_at
                                                    ? \Morilog\Jalali\Jalalian::fromCarbon($fu->due_at)->format('Y/m/d')
                                                    : '—';
                                            @endphp

                                            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                                                <div class="flex items-start justify-between gap-3 mb-2">
                                                    <div class="flex flex-wrap items-center gap-1.5">
                                                        {{-- وضعیت --}}
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full border text-xs
                                        {{ $fu->status === \Modules\Tasks\Entities\Task::STATUS_DONE
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-200 dark:border-emerald-700'
                                            : 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/40 dark:text-amber-200 dark:border-amber-700'
                                        }}">
                                        {{ $statusLabel }}
                                    </span>

                                                        {{-- اولویت --}}
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-[11px]
                                        @if($fu->priority === \Modules\Tasks\Entities\Task::PRIORITY_HIGH || $fu->priority === \Modules\Tasks\Entities\Task::PRIORITY_CRITICAL)
                                            bg-red-50 text-red-700 border-red-100 dark:bg-red-900/40 dark:text-red-200 dark:border-red-700
                                        @elseif($fu->priority === \Modules\Tasks\Entities\Task::PRIORITY_MEDIUM)
                                            bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/40 dark:text-amber-200 dark:border-amber-700
                                        @else
                                            bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700/60 dark:text-gray-200 dark:border-gray-600
                                        @endif">
                                        {{ $priorityLabel }}
                                    </span>
                                                    </div>

                                                    <div class="text-xs text-gray-500 dark:text-gray-400 text-left dir-ltr">
                                                        <div>{{ $dueText }}</div>
                                                        @if($fu->assignee)
                                                            <div class="mt-1 text-[10px] text-gray-500 dark:text-gray-400 dir-rtl text-right">
                                                                مسئول: <span class="font-medium text-gray-700 dark:text-gray-200">{{ $fu->assignee->name }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" title="{{ $fu->title }}">
                                                    {{ $fu->title }}
                                                </div>

                                                @if($fu->description)
                                                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-300 line-clamp-2" title="{{ $fu->description }}">
                                                        {{ $fu->description }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endcan
                    @endif

                    {{-- بخش مدیریت گردش‌کارهای بیمار --}}
                    @if(class_exists(\Modules\Workflows\Entities\Workflow::class))
                        @php
                            $availableWorkflows = \Modules\Workflows\Entities\Workflow::where('is_active', true)->orderBy('name')->get();
                        @endphp
                        <script>
                            function clientWorkflowApp(clientId, availableWorkflows) {
                                return {
                                    clientId: clientId,
                                    availableWorkflows: availableWorkflows,
                                    instances: [],
                                    loading: true,
                                    actionLoading: false,
                                    selectedWorkflowId: '',
                                    showHistory: {},
                                    
                                    async init() {
                                        await this.fetchInstances();
                                    },
                                    
                                    async fetchInstances() {
                                        this.loading = true;
                                        try {
                                            const res = await fetch(`/user/workflows/instances?related_type=CLIENT&related_id=${this.clientId}`);
                                            if (res.ok) {
                                                const data = await res.json();
                                                if (data.success) {
                                                    this.instances = data.instances;
                                                }
                                            }
                                        } catch(e) {
                                            console.error(e);
                                        } finally {
                                            this.loading = false;
                                        }
                                    },
                                    
                                    async startWorkflow() {
                                        if (!this.selectedWorkflowId) return;
                                        this.actionLoading = true;
                                        try {
                                            const res = await fetch('/user/workflows/instances/start', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                                    'X-Requested-With': 'XMLHttpRequest'
                                                },
                                                body: JSON.stringify({
                                                    workflow_id: this.selectedWorkflowId,
                                                    related_type: 'CLIENT',
                                                    related_id: this.clientId
                                                })
                                            });
                                            const data = await res.json();
                                            if (res.ok && data.success) {
                                                this.selectedWorkflowId = '';
                                                await this.fetchInstances();
                                            }
                                        } catch(e) {
                                            console.error(e);
                                        } finally {
                                            this.actionLoading = false;
                                        }
                                    },

                                    async advance(id) {
                                        this.actionLoading = true;
                                        try {
                                            const res = await fetch(`/user/workflows/instances/${id}/advance`, {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                                    'X-Requested-With': 'XMLHttpRequest'
                                                }
                                            });
                                            const data = await res.json();
                                            if (res.ok && data.success) {
                                                await this.fetchInstances();
                                            }
                                        } catch(e) {
                                            console.error(e);
                                        } finally {
                                            this.actionLoading = false;
                                        }
                                    },

                                    async advanceWithChoice(id, varName, value) {
                                        this.actionLoading = true;
                                        try {
                                            const payload = {};
                                            payload[varName] = value;
                                            const res = await fetch(`/user/workflows/instances/${id}/advance`, {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                                    'X-Requested-With': 'XMLHttpRequest'
                                                },
                                                body: JSON.stringify(payload)
                                            });
                                            const data = await res.json();
                                            if (res.ok && data.success) {
                                                await this.fetchInstances();
                                            }
                                        } catch(e) {
                                            console.error(e);
                                        } finally {
                                            this.actionLoading = false;
                                        }
                                    },

                                    async goBack(id) {
                                        if (!confirm('آیا از بازگشت به مرحله قبل اطمینان دارید؟')) return;
                                        this.actionLoading = true;
                                        try {
                                            const res = await fetch(`/user/workflows/instances/${id}/go-back`, {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                                    'X-Requested-With': 'XMLHttpRequest'
                                                }
                                            });
                                            const data = await res.json();
                                            if (res.ok && data.success) {
                                                await this.fetchInstances();
                                            }
                                        } catch(e) {
                                            console.error(e);
                                        } finally {
                                            this.actionLoading = false;
                                        }
                                    },

                                    async cancel(id) {
                                        if (!confirm('آیا از لغو این گردش‌کار اطمینان دارید؟')) return;
                                        this.actionLoading = true;
                                        try {
                                            const res = await fetch(`/user/workflows/instances/${id}/cancel`, {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                                    'X-Requested-With': 'XMLHttpRequest'
                                                }
                                            });
                                            const data = await res.json();
                                            if (res.ok && data.success) {
                                                await this.fetchInstances();
                                            }
                                        } catch(e) {
                                            console.error(e);
                                        } finally {
                                            this.actionLoading = false;
                                        }
                                    },

                                    async restart(id) {
                                        if (!confirm('آیا از شروع مجدد این گردش‌کار اطمینان دارید؟')) return;
                                        this.actionLoading = true;
                                        try {
                                            const res = await fetch(`/user/workflows/instances/${id}/restart`, {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                                    'X-Requested-With': 'XMLHttpRequest'
                                                }
                                            });
                                            const data = await res.json();
                                            if (res.ok && data.success) {
                                                await this.fetchInstances();
                                            }
                                        } catch(e) {
                                            console.error(e);
                                        } finally {
                                            this.actionLoading = false;
                                        }
                                    },

                                    async toggleTaskStatus(taskId) {
                                        this.actionLoading = true;
                                        try {
                                            const res = await fetch(`/user/workflows/tasks/${taskId}/toggle`, {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                                    'X-Requested-With': 'XMLHttpRequest'
                                                }
                                            });
                                            const data = await res.json();
                                            if (res.ok && data.success) {
                                                await this.fetchInstances();
                                            }
                                        } catch(e) {
                                            console.error(e);
                                        } finally {
                                            this.actionLoading = false;
                                        }
                                    },

                                    toggleHistory(id) {
                                        this.showHistory[id] = !this.showHistory[id];
                                    },

                                    getStatusLabel(status) {
                                        if (status === 'ACTIVE') return 'در حال اجرا';
                                        if (status === 'COMPLETED') return 'تکمیل شده';
                                        if (status === 'CANCELED') return 'لغو شده';
                                        return status;
                                    },

                                    getNodeTypeLabel(type) {
                                        if (type === 'START') return 'شروع';
                                        if (type === 'END') return 'پایان';
                                        if (type === 'ACTION') return 'اقدام / وظیفه';
                                        if (type === 'CONDITION') return 'بررسی شرط';
                                        if (type === 'SUB_WORKFLOW') return 'زیر فرآیند';
                                        return type;
                                    },

                                    getConditionVar(inst) {
                                        const expr = inst.current_node_expression || '';
                                        if (expr.includes('=')) {
                                            return expr.split('=')[0].trim();
                                        }
                                        return 'condition_result';
                                    },

                                    getNodeName(inst, nodeId) {
                                        const node = (inst.nodes || []).find(n => String(n.id) === String(nodeId));
                                        if (node) return node.name;
                                        const currentNode = inst.currentNode;
                                        if (currentNode && String(currentNode.id) === String(nodeId)) {
                                            return currentNode.name;
                                        }
                                        return 'شناسه ' + nodeId;
                                    },

                                    getConditionOptions(inst) {
                                        const currentNode = inst.currentNode;
                                        if (!currentNode || currentNode.type !== 'CONDITION') return [];
                                        const edges = (inst.edges || []).filter(e => String(e.source_node_id) === String(currentNode.id));
                                        if (edges.length === 0) {
                                            return [
                                                { label: 'بله / تایید', value: 1 },
                                                { label: 'خیر / رد', value: 0 }
                                            ];
                                        }
                                        return edges.map(e => {
                                            const label = e.condition || 'انتخاب';
                                            let value = 1;
                                            const norm = label.trim().toLowerCase();
                                            if (norm === 'خیر' || norm === 'no' || norm === 'false' || norm === '0' || norm === 'رد') {
                                                value = 0;
                                            }
                                            return { label: label, value: value };
                                        });
                                    },

                                     isCurrentNodeAutoAdvance(inst) {
                                         const currentNode = (inst.nodes || []).find(n => String(n.id) === String(inst.current_node_id));
                                         if (currentNode && currentNode.config) {
                                             const nodeAuto = currentNode.config.auto_advance !== false && currentNode.config.auto_advance !== 'false' && currentNode.config.auto_advance !== 0 && currentNode.config.auto_advance !== '0';
                                              if (!nodeAuto) return false;
                                         }
                                         const currentTasks = (inst.tasks || []).filter(t => String(t.workflow_node_id) === String(inst.current_node_id));
                                         if (currentTasks.some(t => t.auto_advance === false || t.auto_advance === 'false' || t.auto_advance === 0 || t.auto_advance === '0')) {
                                             return false;
                                         }
                                         return true;
                                     },

                                     hasPendingTasks(inst) {
                                        const currentTasks = (inst.tasks || []).filter(t => String(t.workflow_node_id) === String(inst.current_node_id));
                                        return currentTasks.some(t => t.status !== 'DONE');
                                    },

                                    getStepperPath(inst) {
                                        const path = [];
                                        const visitedIds = new Set();
                                        
                                        // Chronological order (reverse of inst.logs since backend returns desc)
                                        const chronologicalLogs = [...(inst.logs || [])].reverse();
                                        chronologicalLogs.forEach(log => {
                                            if (log.to_node_id && log.to_node_id !== inst.current_node_id && !visitedIds.has(log.to_node_id)) {
                                                const node = (inst.nodes || []).find(n => String(n.id) === String(log.to_node_id));
                                                if (node && node.type !== 'START' && node.type !== 'END') {
                                                    path.push({
                                                        id: node.id,
                                                        name: node.name,
                                                        type: node.type,
                                                        status: 'completed',
                                                        date: this.formatDate(log.run_at),
                                                        user: log.user_name
                                                    });
                                                    visitedIds.add(node.id);
                                                }
                                            }
                                        });

                                        // Current active node
                                        if (inst.current_node_id) {
                                            const currNode = (inst.nodes || []).find(n => String(n.id) === String(inst.current_node_id));
                                            if (currNode && currNode.type !== 'START' && currNode.type !== 'END') {
                                                path.push({
                                                    id: currNode.id,
                                                    name: currNode.name,
                                                    type: currNode.type,
                                                    status: 'active'
                                                });
                                                visitedIds.add(currNode.id);
                                            }
                                        }

                                        // Next potential nodes
                                        if (inst.current_node_id && inst.status === 'ACTIVE') {
                                            const nextEdges = (inst.edges || []).filter(e => String(e.source_node_id) === String(inst.current_node_id));
                                            nextEdges.forEach(edge => {
                                                const targetNode = (inst.nodes || []).find(n => String(n.id) === String(edge.target_node_id));
                                                if (targetNode && !visitedIds.has(targetNode.id)) {
                                                    path.push({
                                                        id: targetNode.id,
                                                        name: targetNode.name,
                                                        type: targetNode.type,
                                                        status: 'next',
                                                        condition: edge.condition
                                                    });
                                                }
                                            });
                                        }
                                        
                                        return path;
                                    },

                                    formatDate(isoString) {
                                        if (!isoString) return '—';
                                        try {
                                            const d = new Date(isoString);
                                            return d.toLocaleDateString('fa-IR') + ' ' + d.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
                                        } catch(e) {
                                            return isoString;
                                        }
                                    }
                                };
                            }
                        </script>
                        <div x-data="clientWorkflowApp({{ $client->id }}, @js($availableWorkflows))" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    وضعیت و مسیر گردش‌کارها (بوم بیمار)
                                </h3>
                                <span class="text-xs text-gray-400 dark:text-gray-500" x-text="instances.length + ' فرآیند متصل'"></span>
                            </div>

                            <!-- شروع گردش‌کار جدید -->
                            <div class="p-4 rounded-xl bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100/50 dark:border-indigo-900/30 flex flex-col sm:flex-row gap-3 items-end sm:items-center justify-between">
                                <div class="w-full sm:w-auto flex-1">
                                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 mb-1">راه‌اندازی گردش‌کار جدید برای این بیمار:</div>
                                    <select x-model="selectedWorkflowId" class="w-full sm:w-64 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs px-3 py-2 text-gray-700 dark:text-gray-300 focus:outline-none">
                                        <option value="">— انتخاب گردش‌کار —</option>
                                        <template x-for="wf in availableWorkflows" :key="wf.id">
                                            <option :value="wf.id" x-text="wf.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <button @click="startWorkflow()" :disabled="!selectedWorkflowId || actionLoading" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white disabled:opacity-50 rounded-xl text-xs font-black transition-all flex items-center gap-1.5 shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    شروع فرآیند
                                </button>
                            </div>

                            <!-- لیست فرآیندهای بیمار -->
                            <div class="space-y-4">
                                <template x-if="loading">
                                    <div class="flex flex-col items-center justify-center py-8 gap-3">
                                        <svg class="animate-spin h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-xs text-gray-400 dark:text-gray-500">در حال دریافت وضعیت فرآیندها...</span>
                                    </div>
                                </template>

                                <template x-if="!loading && instances.length === 0">
                                    <div class="text-center py-8 text-sm text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-900/30 rounded-xl">
                                        هیچ گردش‌کاری برای این بیمار شروع نشده است.
                                    </div>
                                </template>

                                <template x-if="!loading && instances.length > 0">
                                    <div class="space-y-6">
                                        <template x-for="inst in instances" :key="inst.id">
                                            <div class="border border-gray-200 dark:border-gray-700/60 rounded-2xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm">
                                                <!-- هدر فرآیند -->
                                                <div class="p-4 bg-gray-50 dark:bg-gray-950/40 flex items-center justify-between border-b border-gray-100 dark:border-gray-700/50">
                                                    <div>
                                                        <span class="text-xs font-extrabold text-gray-800 dark:text-gray-200" x-text="inst.workflow_name"></span>
                                                        <span class="mr-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold tracking-wider"
                                                              :class="{
                                                                  'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400': inst.status === 'ACTIVE',
                                                                  'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400': inst.status === 'COMPLETED',
                                                                  'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400': inst.status === 'CANCELED'
                                                              }" x-text="getStatusLabel(inst.status)"></span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <button @click="toggleHistory(inst.id)" class="text-xs text-gray-500 hover:text-indigo-600 flex items-center gap-1 dark:text-gray-400 font-bold">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            <span>تاریخچه مراحل</span>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- بدنه فرآیند -->
                                                <div class="p-5 space-y-5">
                                                    <!-- بصری‌سازی مسیر فرآیند (Visual Stepper) -->
                                                    <div class="relative w-full overflow-x-auto py-2 px-1 scrollbar-thin" x-show="inst.status === 'ACTIVE'">
                                                        <div class="flex items-center min-w-[600px] justify-between relative py-2">
                                                            <!-- خط زمینه هادی -->
                                                            <div class="absolute top-1/2 left-0 right-0 h-0.5 bg-gray-200 dark:bg-gray-700 -translate-y-1/2 z-0"></div>
                                                            
                                                            <template x-for="(step, sIdx) in getStepperPath(inst)" :key="step.id">
                                                                <div class="flex flex-col items-center relative z-10 px-3 bg-white dark:bg-gray-800">
                                                                    <!-- دایره نماد وضعیت -->
                                                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black transition-all duration-300 border-2"
                                                                         :class="{
                                                                             'bg-emerald-50 border-emerald-500 text-emerald-600 dark:bg-emerald-950/30 dark:border-emerald-500': step.status === 'completed',
                                                                             'bg-indigo-600 border-indigo-600 text-white shadow-sm shadow-indigo-100 dark:shadow-none animate-pulse': step.status === 'active',
                                                                             'bg-white border-gray-300 text-gray-400 dark:bg-gray-800 dark:border-gray-600': step.status === 'next'
                                                                         }">
                                                                        <template x-if="step.status === 'completed'">
                                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                            </svg>
                                                                        </template>
                                                                        <template x-if="step.status === 'active'">
                                                                            <span class="w-2.5 h-2.5 rounded-full bg-white"></span>
                                                                        </template>
                                                                        <template x-if="step.status === 'next'">
                                                                            <span x-text="sIdx + 1"></span>
                                                                        </template>
                                                                    </div>
                                                                    
                                                                    <!-- عنوان گام -->
                                                                    <span class="text-[10px] font-extrabold mt-2 text-center max-w-[120px]"
                                                                          :class="{
                                                                              'text-emerald-600 dark:text-emerald-400': step.status === 'completed',
                                                                              'text-indigo-600 dark:text-indigo-400 font-black': step.status === 'active',
                                                                              'text-gray-500 dark:text-gray-400': step.status === 'next'
                                                                          }" x-text="step.name"></span>
                                                                          
                                                                    <!-- اطلاعات تاریخ تکمیل -->
                                                                    <template x-if="step.status === 'completed'">
                                                                        <span class="text-[8px] text-gray-400 dark:text-gray-500 mt-0.5" x-text="step.date"></span>
                                                                    </template>
                                                                    <!-- برچسب شرط -->
                                                                    <template x-if="step.status === 'next' && step.condition">
                                                                        <span class="text-[8px] px-1 bg-gray-100 dark:bg-gray-700 text-gray-500 rounded mt-0.5" x-text="'شرط: ' + step.condition"></span>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>

                                                    <!-- گام جاری: اطلاعات تفصیلی و کارهای مربوطه -->
                                                    <div class="space-y-4">
                                                        <!-- ۱. گام شرطی (CONDITION) -->
                                                        <template x-if="inst.status === 'ACTIVE' && inst.current_node_type === 'CONDITION'">
                                                            <div class="p-4 bg-amber-50/50 dark:bg-amber-950/10 border border-amber-100 dark:border-amber-900/30 rounded-2xl space-y-4">
                                                                <div class="flex items-center gap-2">
                                                                    <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                        </svg>
                                                                    </div>
                                                                    <div>
                                                                        <div class="text-[10px] font-bold text-gray-500 dark:text-gray-400">بررسی و تصمیم‌گیری شرط</div>
                                                                        <div class="text-xs font-black text-gray-800 dark:text-gray-100" x-text="'شرط جاری: ' + inst.current_node_name"></div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                                                                    مسیر فرآیند در انتظار پاسخ شما به این شرط است. لطفاً یکی از گزینه‌های معتبر زیر را انتخاب کنید تا درمان طبق مسیر متناظر هدایت شود:
                                                                </p>

                                                                <div class="flex flex-wrap gap-2 pt-1">
                                                                    <template x-for="opt in getConditionOptions(inst)" :key="opt.label">
                                                                        <button @click="advanceWithChoice(inst.id, getConditionVar(inst), opt.value)" 
                                                                                :disabled="actionLoading"
                                                                                class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border shadow-sm"
                                                                                :class="opt.value === 1 
                                                                                    ? 'bg-emerald-600 hover:bg-emerald-700 border-emerald-600 text-white shadow-emerald-100 dark:shadow-none' 
                                                                                    : 'bg-rose-600 hover:bg-rose-700 border-rose-600 text-white shadow-rose-100 dark:shadow-none'">
                                                                            <span x-text="opt.label"></span>
                                                                        </button>
                                                                    </template>
                                                                </div>
                                                                
                                                                <!-- نمایش شرط فنی به صورت جمع‌شونده -->
                                                                <div x-data="{ open: false }" class="border-t border-gray-100 dark:border-gray-800/80 pt-2.5">
                                                                    <button @click="open = !open" class="text-[10px] text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 flex items-center gap-1">
                                                                        <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                                        </svg>
                                                                        مشاهده جزئیات فنی شرط
                                                                    </button>
                                                                    <div x-show="open" x-collapse class="mt-2 text-[10px] bg-white dark:bg-gray-800 p-2.5 rounded-lg font-mono text-gray-500 dark:text-gray-400 border dark:border-gray-700">
                                                                        Expression: <span x-text="inst.current_node_expression"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>

                                                        <!-- ۲. گام اقدام (ACTION یا START) -->
                                                        <template x-if="inst.status === 'ACTIVE' && inst.current_node_type !== 'CONDITION'">
                                                            <div class="bg-indigo-50/30 dark:bg-indigo-950/5 border border-indigo-100/50 dark:border-indigo-900/30 rounded-2xl p-4 space-y-4">
                                                                <div class="flex items-center justify-between border-b border-indigo-100/40 dark:border-indigo-900/20 pb-3">
                                                                    <div class="flex items-center gap-2">
                                                                        <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2H9a2 2 0 00-2 2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                            </svg>
                                                                        </div>
                                                                        <div>
                                                                            <div class="text-[10px] font-bold text-gray-500 dark:text-gray-400">اقدام جاری فرآیند</div>
                                                                            <div class="text-xs font-black text-gray-850 dark:text-gray-150" x-text="inst.current_node_name"></div>
                                                                        </div>
                                                                    </div>
                                                                    <span class="text-[10px] px-2.5 py-0.5 rounded bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400 font-bold" x-text="getNodeTypeLabel(inst.current_node_type)"></span>
                                                                </div>

                                                                <!-- لیست وظایف مربوط به این گام -->
                                                                <div class="space-y-3">
                                                                    <div class="text-[11px] font-bold text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                        </svg>
                                                                        وظایف و کارهای مورد نیاز این مرحله:
                                                                    </div>
                                                                    
                                                                    <!-- اگر فاقد تسک باشد -->
                                                                    <template x-if="inst.tasks.filter(t => String(t.workflow_node_id) === String(inst.current_node_id)).length === 0">
                                                                        <div class="text-xs text-gray-400 dark:text-gray-500 italic p-3 bg-gray-50 dark:bg-gray-900/20 rounded-xl border dark:border-gray-800">
                                                                            این گام فاقد کار دستی است. جهت هدایت به مرحله بعد، روی دکمه «تایید و رفتن به گام بعد» کلیک کنید.
                                                                        </div>
                                                                    </template>

                                                                    <!-- لیست تسک‌های جاری با امکان کلیک و چک‌باکس مستقیم -->
                                                                    <template x-if="inst.tasks.filter(t => String(t.workflow_node_id) === String(inst.current_node_id)).length > 0">
                                                                        <div class="grid grid-cols-1 gap-2">
                                                                            <template x-for="task in inst.tasks.filter(t => String(t.workflow_node_id) === String(inst.current_node_id))" :key="task.id">
                                                                                <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800/80 border border-gray-100 dark:border-gray-700/60 rounded-xl hover:shadow-sm transition-all duration-200">
                                                                                    <div class="flex items-center gap-3">
                                                                                        <input type="checkbox" 
                                                                                               :checked="task.status === 'DONE'" 
                                                                                               @change="toggleTaskStatus(task.id)"
                                                                                               :disabled="actionLoading"
                                                                                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer disabled:opacity-50">
                                                                                        <span :class="task.status === 'DONE' ? 'line-through text-gray-400 dark:text-gray-500 font-normal' : 'text-gray-700 dark:text-gray-200 font-semibold'" class="text-xs" x-text="task.title"></span>
                                                                                    </div>
                                                                                    <div class="flex items-center gap-2">
                                                                                        <span class="text-[10px] px-2 py-0.5 rounded bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300 font-medium" x-text="task.assignee_name"></span>
                                                                                        <span class="text-[9px] font-extrabold" 
                                                                                              :class="task.status === 'DONE' ? 'text-emerald-500' : 'text-amber-500'" 
                                                                                              x-text="task.status === 'DONE' ? 'انجام شد' : 'در صف انجام'"></span>
                                                                                    </div>
                                                                                </div>
                                                                            </template>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>

                                                    <!-- عملیات کنترل و ناوبری فرآیند -->
                                                    <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100 dark:border-gray-800/80">
                                                        <!-- دکمه هدایت دستی به گام بعد -->
                                                        <template x-if="inst.status === 'ACTIVE' && inst.current_node_type !== 'CONDITION' && !isCurrentNodeAutoAdvance(inst)">
                                                            <button @click="advance(inst.id)" :disabled="actionLoading" 
                                                                    class="px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-1.5 shadow-sm"
                                                                    :class="hasPendingTasks(inst)
                                                                        ? 'bg-white border border-amber-300 text-amber-700 hover:bg-amber-50 dark:bg-amber-950/20 dark:border-amber-900 dark:text-amber-400 dark:hover:bg-amber-900/30'
                                                                        : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-150 dark:shadow-none'">
                                                                <template x-if="hasPendingTasks(inst)">
                                                                    <svg class="w-4 h-4 text-amber-500 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                                    </svg>
                                                                </template>
                                                                <span x-text="hasPendingTasks(inst) ? 'تایید و عبور (با وجود کارهای ناقص)' : 'تایید و رفتن به گام بعد'"></span>
                                                            </button>
                                                        </template>
                                                        
                                                        <template x-if="inst.status === 'ACTIVE'">
                                                            <button @click="goBack(inst.id)" :disabled="actionLoading" class="px-3 py-2 bg-white border border-gray-250 text-gray-700 hover:bg-gray-50 rounded-xl text-xs font-bold transition-all dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                                                                بازگشت به گام قبلی
                                                            </button>
                                                        </template>

                                                        <template x-if="inst.status === 'ACTIVE'">
                                                            <button @click="cancel(inst.id)" :disabled="actionLoading" class="px-3 py-2 bg-red-50 text-red-700 hover:bg-red-100 border border-red-100 dark:bg-red-950/20 dark:text-red-400 dark:border-red-900/30 rounded-xl text-xs font-bold transition-all mr-auto">
                                                                لغو این گردش‌کار
                                                            </button>
                                                        </template>

                                                        <template x-if="inst.status !== 'ACTIVE'">
                                                            <button @click="restart(inst.id)" :disabled="actionLoading" class="px-3 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-100 dark:bg-indigo-950/20 dark:text-indigo-400 dark:border-indigo-900/30 rounded-xl text-xs font-black transition-all">
                                                                شروع مجدد فرآیند درمان
                                                            </button>
                                                        </template>
                                                    </div>

                                                    <!-- تاریخچه مراحل طی شده به صورت خط زمانی مدرن -->
                                                    <div x-show="showHistory[inst.id]" x-collapse class="p-4 bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-100 dark:border-gray-800/80 mt-4 space-y-3">
                                                        <div class="text-xs font-bold text-gray-500 dark:text-gray-400 pb-2 border-b border-gray-150 dark:border-gray-800/80 flex items-center gap-1.5">
                                                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            تاریخچه مراحل طی شده:
                                                        </div>
                                                        <div class="space-y-4 relative pr-4 border-r-2 border-indigo-100 dark:border-indigo-900/40 mr-1 mt-2">
                                                            <template x-for="log in inst.logs" :key="log.id">
                                                                <div class="relative">
                                                                    <!-- دایره خط زمانی -->
                                                                    <div class="absolute -right-[22px] top-1 w-3.5 h-3.5 rounded-full bg-white dark:bg-gray-800 border-2 border-indigo-500 flex items-center justify-center z-10">
                                                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                                                    </div>
                                                                    <div class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                                                        انتقال به گام: <span class="text-indigo-650 dark:text-indigo-400 font-extrabold" x-text="log.to_node_id ? getNodeName(inst, log.to_node_id) : 'پایان'"></span>
                                                                        <template x-if="log.from_node_id">
                                                                            <span class="text-[10px] text-gray-400 font-normal" x-text="' (از ' + getNodeName(inst, log.from_node_id) + ')'"></span>
                                                                        </template>
                                                                    </div>
                                                                    <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 flex items-center gap-3">
                                                                        <span class="flex items-center gap-1">
                                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                                            </svg>
                                                                            <span class="font-semibold text-gray-600 dark:text-gray-400" x-text="log.user_name || 'سیستم'"></span>
                                                                        </span>
                                                                        <span>•</span>
                                                                        <span class="flex items-center gap-1">
                                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                            </svg>
                                                                            <span x-text="formatDate(log.run_at)"></span>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ستون فیلدهای سفارشی --}}
                <div
                        class="lg:col-span-1 border-t lg:border-t-0 lg:border-r border-gray-100 dark:border-gray-700 lg:pr-8 pt-8 lg:pt-0">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        اطلاعات تکمیلی
                    </h3>

                    @php
                        $schemaFields = isset($activeForm) && isset($activeForm->schema['fields']) ? $activeForm->schema['fields'] : [];
                        $systemFieldIds = ['full_name', 'phone', 'email', 'national_code', 'case_number', 'notes', 'status_id', 'password'];
                        
                        // فیلتر کردن فیلدهای سفارشی که در اسکیمای فعال فرم وجود دارند و مقدار غیرخالی دارند
                        $customMetaFields = collect($schemaFields)->filter(function($f) use ($client, $systemFieldIds) {
                            $fid = $f['id'] ?? null;
                            if (!$fid || in_array($fid, $systemFieldIds, true)) {
                                return false;
                            }
                            return isset($client->meta[$fid]) && $client->meta[$fid] !== '' && $client->meta[$fid] !== [];
                        });
                    @endphp

                    @if($customMetaFields->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($customMetaFields as $fieldDef)
                                @php
                                    $k = $fieldDef['id'];
                                    $v = $client->meta[$k];
                                    $fieldLabel = $fieldDef['label'] ?? $k;
                                    $fieldType = $fieldDef['type'] ?? 'text';
                                    
                                    // استخراج گزینه‌ها در صورت وجود
                                    $options = [];
                                    $optsJson = $fieldDef['options_json'] ?? '';
                                    if (is_string($optsJson) && trim($optsJson) !== '') {
                                        $decodedOpts = json_decode($optsJson, true);
                                        if (is_array($decodedOpts)) {
                                            $options = $decodedOpts;
                                        } else {
                                            $lines = array_filter(array_map('trim', explode("\n", $optsJson)));
                                            foreach ($lines as $line) {
                                                if (str_contains($line, ':')) {
                                                    [$okey, $oval] = array_map('trim', explode(':', $line, 2));
                                                    $options[$okey] = $oval;
                                                } else {
                                                    $options[$line] = $line;
                                                }
                                            }
                                        }
                                    }

                                    // تابع کمکی برای پیدا کردن عنوان نمایشی مقدار
                                    $getDisplayVal = function($val) use ($options) {
                                        $valStr = (string)$val;
                                        if (isset($options[$valStr])) {
                                            return $options[$valStr];
                                        }
                                        // جستجوی معکوس اگر کلیدها با مقادیر جابجا تعریف شده باشند
                                        $flipped = array_flip($options);
                                        if (isset($flipped[$valStr])) {
                                            return $valStr; // کلید در flipped مقدار است
                                        }
                                        return $val;
                                    };
                                @endphp
                                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $fieldLabel }}</div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-200 break-words">
                                        @if($fieldType === 'file')
                                            @php
                                                $files = [];
                                                if (is_array($v)) {
                                                    $files = $v;
                                                } else {
                                                    $decoded = json_decode($v, true);
                                                    if (is_array($decoded)) {
                                                        $files = $decoded;
                                                    } elseif (!empty($v)) {
                                                        $files = [$v];
                                                    }
                                                }
                                            @endphp
                                            @if(empty($files))
                                                <span class="text-gray-400">—</span>
                                            @else
                                                <div class="flex flex-wrap gap-2 mt-1">
                                                    @foreach($files as $file)
                                                        @php
                                                            $fileUrl = Storage::disk('public')->url($file);
                                                            $fileName = basename($file);
                                                            $isImg = in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                                                        @endphp
                                                        <a href="{{ $fileUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-xs font-medium text-gray-700 hover:text-indigo-600 hover:border-indigo-300 transition-colors dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:text-indigo-400 shadow-sm">
                                                            @if($isImg)
                                                                 <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                </svg>
                                                            @else
                                                                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                </svg>
                                                            @endif
                                                            <span class="truncate max-w-[150px] dir-ltr text-left">{{ $fileName }}</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @elseif($fieldType === 'select-province-city' && is_string($v))
                                            @php
                                                $decoded = json_decode($v, true);
                                            @endphp
                                            @if(is_array($decoded))
                                                {{ $decoded['province'] ?? '' }} - {{ $decoded['city'] ?? '' }}
                                            @else
                                                {{ $v }}
                                            @endif
                                        @elseif(is_array($v))
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @foreach($v as $item)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-white border border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                                                        {{ is_string($item) ? $getDisplayVal($item) : json_encode($item) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @elseif(is_bool($v))
                                            <span class="{{ $v ? 'text-emerald-600' : 'text-red-600' }}">
                                                {{ $v ? 'بله' : 'خیر' }}
                                            </span>
                                        @else
                                            {{-- فیلدهای عادی، رادیو یا سلکت تک انتخابی --}}
                                            @if(is_string($v) && str_starts_with($v, '[') && str_ends_with($v, ']'))
                                                @php
                                                    $decodedArr = json_decode($v, true);
                                                @endphp
                                                @if(is_array($decodedArr))
                                                    <div class="flex flex-wrap gap-1 mt-1">
                                                        @foreach($decodedArr as $item)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-white border border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                                                                {{ $getDisplayVal($item) }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    {{ $getDisplayVal($v) ?: '—' }}
                                                @endif
                                            @else
                                                {{ $getDisplayVal($v) ?: '—' }}
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div
                                class="text-center py-6 text-sm text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-900/30 rounded-xl">
                            اطلاعات اضافی ثبت نشده است.
                        </div>
                    @endif
                </div>

            </div>

            {{-- فوتر (متادیتای سیستم) --}}
            <div
                    class="bg-gray-50 dark:bg-gray-900/40 px-6 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-4 justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                <div class="flex gap-4">
                    <span>شناسه سیستمی: <span class="font-mono">{{ $client->id }}</span></span>
                    @if($client->created_at)
                        {{-- <span>تاریخ ثبت: <span class="dir-ltr">{{ $client->created_at->toJalali()->format('Y/m/d H:i') }}</span></span> --}}
                    @endif
                </div>
                @if(optional($client->creator)->name)
                    <div>
                        ثبت شده توسط: {{ $client->creator->name }}
                    </div>
                @endif
            </div>

        </div>
    </div>

@endsection
