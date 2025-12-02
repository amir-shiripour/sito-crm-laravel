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
@endphp

@section('content')
    <div class="mx-auto max-w-full space-y-6">
        {{-- کارت اصلی --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">

            {{-- هدر پروفایل --}}
            <div class="relative bg-gray-50/50 dark:bg-gray-900/30 border-b border-gray-200 dark:border-gray-700 p-6 sm:p-8">
                <div class="flex flex-col-2 sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        {{-- آواتار حروف اول --}}
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300 text-2xl font-bold ring-4 ring-white dark:ring-gray-800">
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
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span>{{ $client->username }}</span>
                                </div>

                                {{-- بج وضعیت --}}
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-xs font-medium {{ $statusBadgeClasses }}">
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>بازگشت</span>
                        </a>

                        @can('clients.edit')
                            <a href="{{ route('user.clients.edit', $client) }}"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
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
                            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">ایمیل</div>
                                <div class="font-medium text-gray-900 dark:text-gray-200 dir-ltr break-all flex items-center gap-2">
                                    @if($client->email)
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        {{ $client->email }}
                                    @else
                                        <span class="text-gray-400 italic">—</span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">تلفن تماس</div>
                                <div class="font-medium text-gray-900 dark:text-gray-200 dir-ltr text-right flex items-center justify-end gap-2">
                                    @if($client->phone)
                                        {{ $client->phone }}
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                    @else
                                        <span class="text-gray-400 italic">—</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- بخش یادداشت --}}
                    @if($client->notes)
                        <section>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>
                                یادداشت‌ها
                            </h3>
                            <div class="p-4 rounded-xl bg-yellow-50 border border-yellow-100 text-yellow-900 dark:bg-yellow-900/20 dark:border-yellow-900/30 dark:text-yellow-200 text-sm leading-relaxed whitespace-pre-wrap">
                                {{ $client->notes }}
                            </div>
                        </section>
                    @endif

                </div>

                {{-- ستون فیلدهای سفارشی --}}
                <div class="lg:col-span-1 border-t lg:border-t-0 lg:border-r border-gray-100 dark:border-gray-700 lg:pr-8 pt-8 lg:pt-0">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        اطلاعات تکمیلی
                    </h3>

                    @if(is_array($client->meta) && count($client->meta))
                        <div class="space-y-4">
                            @foreach($client->meta as $k => $v)
                                <div class="relative pl-3 before:absolute before:right-0 before:top-1.5 before:h-1.5 before:w-1.5 before:rounded-full before:bg-gray-300 dark:before:bg-gray-600">
                                    <dt class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $k }}</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-200 break-words">
                                        @if(is_array($v))
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @foreach($v as $item)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                        {{ is_string($item) ? $item : json_encode($item) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @elseif(is_bool($v))
                                            <span class="{{ $v ? 'text-emerald-600' : 'text-red-600' }}">
                                                {{ $v ? 'بله' : 'خیر' }}
                                            </span>
                                        @else
                                            {{ $v ?: '—' }}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-sm text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-900/30 rounded-xl">
                            اطلاعات اضافی ثبت نشده است.
                        </div>
                    @endif
                </div>

            </div>

            {{-- فوتر (متادیتای سیستم) --}}
            <div class="bg-gray-50 dark:bg-gray-900/40 px-6 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-4 justify-between items-center text-xs text-gray-500 dark:text-gray-400">
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
