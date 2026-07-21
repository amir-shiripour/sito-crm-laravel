@extends('layouts.user')
@section('title', 'پروژه: ' . $project->name)

@php
    $cardClass = "bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden";
@endphp

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $project->name }}</h1>
                    @if($project->status)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border"
                              style="background: {{ $project->status->color }}1a; color: {{ $project->status->color }}; border-color: {{ $project->status->color }}33">
                            <span class="w-1.5 h-1.5 rounded-full"
                                  style="background: {{ $project->status->color }}"></span>
                            {{ $project->status->name }}
                        </span>
                    @endif
                </div>
                @if($project->code)
                    <p class="text-xs font-mono text-gray-400 mt-1">{{ $project->code }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @can('update', $project)
                    <a href="{{ route('services.projects.edit', $project) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 text-sm font-bold transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        ویرایش
                    </a>
                @endcan
                <a href="{{ route('services.projects.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400 transition-colors group">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    بازگشت
                </a>
            </div>
        </div>

    @if(session('success'))
            <div
                class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div
                class="rounded-2xl bg-red-50 p-4 border border-red-100 dark:bg-red-900/20 dark:border-red-800/50 text-sm text-red-700 dark:text-red-400 flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="font-bold mb-1">خطا در ثبت اطلاعات</p>
                    <ul class="list-disc pr-4 space-y-1">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Progress bar --}}
        <div class="{{ $cardClass }}">
            <div class="p-5 flex items-center justify-between mb-2">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    پیشرفت پروژه
                </h3>
                <span
                    class="text-sm font-mono font-bold text-purple-600 dark:text-purple-400">{{ $project->progress }}%</span>
            </div>
            <div class="px-5 pb-5">
                <div class="w-full h-3 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-purple-500 to-purple-400 rounded-full transition-all"
                         style="width: {{ $project->progress }}%"></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left column --}}
            <div class="lg:col-span-1 space-y-6 order-2 lg:order-1">

                {{-- Info card --}}
                <div class="{{ $cardClass }}">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700/50">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            اطلاعات پروژه
                        </h3>
                    </div>
                    <div class="p-5 space-y-4 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400">مشتری</span>
                            <span
                                class="font-bold text-gray-900 dark:text-white">{{ $project->customer->name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400">سرویس</span>
                            <span
                                class="font-bold text-gray-900 dark:text-white">{{ $project->service->name ?? '—' }}</span>
                        </div>
                        @if($project->assignedUser)
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 dark:text-gray-400">مسئول</span>
                                <span
                                    class="font-bold text-gray-900 dark:text-white">{{ $project->assignedUser->name }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400">اولویت</span>
                            @php
                                $priorityConfig = match($project->priority) {
                                    'urgent' => ['label' => 'فوری',  'class' => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400'],
                                    'high'   => ['label' => 'زیاد',  'class' => 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400'],
                                    'medium' => ['label' => 'متوسط', 'class' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400'],
                                    default  => ['label' => 'کم',    'class' => 'bg-gray-100 text-gray-600 dark:bg-gray-700/50 dark:text-gray-400'],
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $priorityConfig['class'] }}">{{ $priorityConfig['label'] }}</span>
                        </div>
                        @if($project->budget)
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 dark:text-gray-400">بودجه</span>
                                <span
                                    class="font-mono font-bold text-gray-900 dark:text-white">{{ number_format($project->budget) }}</span>
                            </div>
                        @endif
                        <div class="border-t border-dashed border-gray-200 dark:border-gray-700 pt-4 space-y-3">
                            @if($project->start_date)
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500 dark:text-gray-400">شروع</span>
                                    <span
                                        class="font-mono text-xs text-gray-700 dark:text-gray-300 dir-ltr">{{ $project->start_date->format('Y-m-d') }}</span>
                                </div>
                            @endif
                            @if($project->end_date)
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500 dark:text-gray-400">پایان</span>
                                    <span
                                        class="font-mono text-xs {{ $project->end_date->isPast() && $project->progress < 100 ? 'text-red-500 font-bold' : 'text-gray-700 dark:text-gray-300' }} dir-ltr">
                                        {{ $project->end_date->format('Y-m-d') }}
                                    </span>
                                </div>
                                @endif
                        </div>
                    </div>
                </div>

                {{-- Change status --}}
                @can('update', $project)
                    @if($nextStatuses->isNotEmpty())
                        <div class="{{ $cardClass }}">
                            <div class="p-5 border-b border-gray-100 dark:border-gray-700/50">
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                    تغییر وضعیت
                                </h3>
                            </div>
                            <div class="p-5">
                                <form method="POST" action="{{ route('services.projects.change-status', $project) }}">
                                    @csrf
                                    <div class="flex gap-2">
                                        <select name="status_id"
                                                class="flex-1 rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 px-3 py-2.5 text-sm focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all dark:text-white">
                                            @foreach($nextStatuses as $st)
                                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit"
                                                class="px-4 py-2.5 rounded-xl bg-purple-600 text-white text-sm font-bold hover:bg-purple-700 transition-all active:scale-95">
                                            ثبت
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @endcan

            </div>

            {{-- Right column --}}
            <div class="lg:col-span-2 space-y-6 order-1 lg:order-2">

                {{-- Description --}}
                <div class="{{ $cardClass }}">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700/50">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            توضیحات
                        </h3>
                    </div>
                    <div class="p-5">
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed whitespace-pre-line">
                            {{ $project->description ?: 'توضیحاتی ثبت نشده است.' }}
                        </p>
                    </div>
                </div>

                {{-- Invoices --}}
                @if($project->invoices->isNotEmpty())
                    <div class="{{ $cardClass }}">
                        <div
                            class="p-5 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                فاکتورهای مرتبط
                            </h3>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @foreach($project->invoices as $invoice)
                                <div
                                    class="p-5 flex items-center justify-between hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-mono text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ $invoice->invoice_number }}</span>
                                        <span
                                            class="text-xs text-gray-400 mt-1 dir-ltr">{{ $invoice->issue_date?->format('Y-m-d') }}</span>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        @if($invoice->status)
                                            <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                                                  style="background: {{ $invoice->status->color }}1a; color: {{ $invoice->status->color }}">
                                                {{ $invoice->status->name }}
                                            </span>
                                        @endif
                                        <span
                                            class="font-mono text-sm font-bold text-gray-700 dark:text-gray-300">{{ number_format($invoice->total) }}</span>
                                        <a href="{{ route('services.invoices.show', $invoice) }}"
                                           class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-bold flex items-center gap-1">
                                            مشاهده
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Activity log --}}
                @if($project->activities->isNotEmpty())
                    <div class="{{ $cardClass }}">
                        <div class="p-5 border-b border-gray-100 dark:border-gray-700/50">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                تاریخچه فعالیت
                            </h3>
                        </div>
                        <div class="p-5 space-y-4">
                            @foreach($project->activities->take(10) as $log)
                                <div class="flex items-start gap-3 text-sm">
                                    <div class="relative flex-shrink-0 mt-1.5">
                                        <div class="w-2.5 h-2.5 rounded-full bg-purple-500"></div>
                                        @if(!$loop->last)
                                            <div
                                                class="absolute top-3.5 right-1/2 translate-x-1/2 w-px h-full bg-gray-200 dark:bg-gray-700"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0 pb-4">
                                        <span class="text-gray-700 dark:text-gray-300 block">{{ $log->description ?? $log->action }}</span>
                                        <div class="flex items-center gap-2 mt-1">
                                            @if($log->user)
                                                <span class="text-gray-400 text-xs">{{ $log->user->name }}</span>
                                            @endif
                                            <span class="text-gray-300 dark:text-gray-600">|</span>
                                            <span
                                                class="text-xs text-gray-400 font-mono dir-ltr">{{ $log->created_at->format('Y-m-d H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
