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
