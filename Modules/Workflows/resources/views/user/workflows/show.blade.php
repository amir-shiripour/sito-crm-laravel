@extends('layouts.user')

@section('title', 'جزئیات گردش کار')

@section('content')
    <div class="max-w-5xl mx-auto space-y-8 pb-10">
        
        {{-- Header Card --}}
        <div class="bg-white dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60 rounded-2xl shadow-sm overflow-hidden relative">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
            <div class="p-6 sm:p-8 flex flex-col sm:flex-row sm:items-start justify-between gap-6">
                <div>
                    <div class="flex flex-wrap items-center gap-3 mb-2">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                            {{ $workflow->name }}
                        </h1>
                        @if($workflow->is_active)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20 shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                فعال
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-500/10 dark:text-slate-400 border border-slate-200/60 dark:border-slate-500/20 shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                غیرفعال
                            </span>
                        @endif
                    </div>
                    <p class="text-base text-slate-500 dark:text-slate-400 leading-relaxed max-w-3xl">
                        {{ $workflow->description ?: 'توضیحاتی برای این گردش کار ثبت نشده است.' }}
                    </p>
                    <div class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300 font-mono shadow-inner">
                        <span class="text-slate-400">شناسه سیستم:</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 select-all">{{ $workflow->key }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-3 sm:flex-col sm:items-end">
                    @can('workflows.manage')
                        <a href="{{ route('user.workflows.edit', $workflow) }}" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium shadow-sm shadow-indigo-600/20 transition-all focus:ring-4 focus:ring-indigo-500/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            ویرایش گردش کار
                        </a>
                    @endcan
                    <a href="{{ route('user.workflows.index') }}" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium shadow-sm transition-all focus:ring-4 focus:ring-slate-500/10">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        بازگشت به لیست
                    </a>
                </div>
            </div>
        </div>

        {{-- Triggers Info --}}
        @if($workflow->triggers->isNotEmpty())
            <div class="bg-white dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60 rounded-2xl shadow-sm p-6 sm:p-8">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center border border-amber-100 dark:border-amber-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    شرایط شروع (Triggers)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($workflow->triggers as $trigger)
                        <div class="group relative bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl p-5 hover:border-amber-300 dark:hover:border-amber-500/50 transition-colors">
                            <div class="font-bold text-gray-900 dark:text-white text-base mb-3 flex items-center gap-2">
                                @if($trigger->type === 'SCHEDULE')
                                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    زمان‌بندی شده (Cron)
                                @elseif($trigger->type === 'APPOINTMENT_REMINDER')
                                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    یادآوری نوبت
                                @elseif($trigger->type === 'EVENT')
                                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    رویداد سیستمی
                                @else
                                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>
                                    {{ $trigger->type }}
                                @endif
                            </div>

                            <div class="space-y-2">
                                @if(empty($trigger->config))
                                    <span class="text-sm text-slate-400 italic">بدون تنظیمات اضافی</span>
                                @else
                                    @foreach($trigger->config as $key => $val)
                                        <div class="flex items-start gap-2 text-sm">
                                            <span class="font-medium text-slate-500 dark:text-slate-400 min-w-[80px] pt-0.5">{{ Str::title(str_replace('_', ' ', $key)) }}:</span>
                                            <span class="text-slate-800 dark:text-slate-200 font-mono break-all bg-white dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-100 dark:border-slate-700/50 flex-1">
                                                {{ is_array($val) || is_object($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val }}
                                            </span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Stages Timeline --}}
        <div class="bg-white dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60 rounded-2xl shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-8 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center border border-indigo-100 dark:border-indigo-500/20">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
                مراحل اجرا و عملیات‌ها
            </h2>

            @if($workflow->stages->isEmpty())
                <div class="text-center py-16 bg-slate-50 dark:bg-slate-900/50 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl">
                    <div class="mx-auto w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-lg font-medium">هیچ مرحله‌ای برای این گردش کار تعریف نشده است.</p>
                </div>
            @else
                <div class="relative mr-4 lg:mr-8 space-y-10 py-4 before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 dark:before:via-slate-700 before:to-transparent">
                    @foreach($workflow->stages as $stage)
                        <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                            
                            {{-- Timeline Node --}}
                            <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white dark:border-slate-800 {{ $stage->is_initial ? 'bg-emerald-500 text-emerald-50' : ($stage->is_final ? 'bg-slate-600 text-slate-50' : 'bg-indigo-500 text-indigo-50') }} shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 absolute top-0 -right-[20px] md:relative md:top-auto md:right-auto z-10">
                                @if($stage->is_initial)
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                @elseif($stage->is_final)
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10h6v4H9z" /></svg>
                                @else
                                    <span class="text-sm font-bold">{{ $stage->sort_order }}</span>
                                @endif
                            </div>

                            {{-- Stage Content Card --}}
                            <div class="w-full pr-8 md:pr-0 md:w-[calc(50%-2.5rem)]">
                                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden">
                                    {{-- Stage Header --}}
                                    <div class="bg-slate-50 dark:bg-slate-900/50 px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-bold text-slate-900 dark:text-white text-base">
                                                {{ $stage->name }}
                                            </h3>
                                            @if($stage->is_initial)
                                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold tracking-wider uppercase bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400">شروع (Initial)</span>
                                            @endif
                                            @if($stage->is_final)
                                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold tracking-wider uppercase bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300">پایان (Final)</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Stage Actions --}}
                                    <div class="p-5">
                                        @if($stage->actions->isEmpty())
                                            <div class="text-center py-4 text-slate-400 dark:text-slate-500 text-sm italic">
                                                عملیاتی در این مرحله تعریف نشده است.
                                            </div>
                                        @else
                                            <div class="space-y-4">
                                                @foreach($stage->actions as $action)
                                                    <div class="relative bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-xl p-4 shadow-sm group-hover:border-indigo-100 dark:group-hover:border-indigo-500/30 transition-colors">
                                                        
                                                        <div class="flex items-start gap-3">
                                                            <div class="mt-0.5 flex-shrink-0 w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-100 dark:border-indigo-500/20">
                                                                @if($action->action_type === 'SEND_SMS')
                                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                                                                @elseif($action->action_type === 'CREATE_TASK')
                                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                                                                @else
                                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                                                @endif
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <div class="font-bold text-slate-800 dark:text-slate-100 text-sm mb-1">
                                                                    @if($action->action_type === 'SEND_SMS') ارسال پیامک
                                                                    @elseif($action->action_type === 'CREATE_TASK') ایجاد وظیفه
                                                                    @else {{ $action->action_type }}
                                                                    @endif
                                                                </div>
                                                                
                                                                @if(!empty($action->config['title']))
                                                                    <div class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 mb-2">
                                                                        {{ $action->config['title'] }}
                                                                    </div>
                                                                @endif

                                                                @if(!empty($action->config))
                                                                    <div class="mt-2 space-y-1.5 bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-lg border border-slate-100 dark:border-slate-700/50">
                                                                        @foreach($action->config as $key => $val)
                                                                            @if($key !== 'title')
                                                                                <div class="flex items-start gap-2 text-xs">
                                                                                    <span class="font-semibold text-slate-500 dark:text-slate-400 min-w-[70px] pt-0.5">{{ Str::title(str_replace('_', ' ', $key)) }}:</span>
                                                                                    <div class="flex-1 text-slate-700 dark:text-slate-300 font-mono break-words leading-relaxed">
                                                                                        @if(is_array($val) || is_object($val))
                                                                                            <pre class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-1.5 rounded-md overflow-x-auto m-0 mt-1">{{ json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                                        @else
                                                                                            <span class="bg-white dark:bg-slate-800 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700/60 inline-block">{{ $val }}</span>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
