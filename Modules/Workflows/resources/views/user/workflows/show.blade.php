@extends('layouts.user')

@section('title', 'جزئیات گردش کار')

@section('content')
    <div class="max-w-5xl mx-auto space-y-8 pb-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    {{ $workflow->name }}
                    @if($workflow->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                            فعال
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                            غیرفعال
                        </span>
                    @endif
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-2xl">
                    {{ $workflow->description ?: 'بدون توضیحات.' }}
                </p>
                <div class="mt-2 flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500 font-mono">
                    <span>KEY: {{ $workflow->key }}</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('user.workflows.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                    بازگشت به لیست
                </a>
                @can('workflows.manage')
                    <a href="{{ route('user.workflows.edit', $workflow) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        ویرایش گردش کار
                    </a>
                @endcan
            </div>
        </div>

        {{-- Triggers Info --}}
        @if($workflow->triggers->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    شرایط شروع (Triggers)
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($workflow->triggers as $trigger)
                        <div class="bg-gray-50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-700 rounded-lg p-3 text-sm">
                            <div class="font-medium text-gray-900 dark:text-white mb-1">
                                @if($trigger->type === 'SCHEDULE')
                                    زمان‌بندی شده (Cron)
                                @elseif($trigger->type === 'APPOINTMENT_REMINDER')
                                    یادآوری نوبت
                                @elseif($trigger->type === 'EVENT')
                                    رویداد سیستمی
                                @else
                                    {{ $trigger->type }}
                                @endif
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-mono dir-ltr">
                                {{ json_encode($trigger->config, JSON_UNESCAPED_UNICODE) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Stages Timeline --}}
        <div class="space-y-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span>
                مراحل اجرا
            </h2>

            @if($workflow->stages->isEmpty())
                <div class="text-center py-12 bg-white dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl">
                    <p class="text-gray-500 dark:text-gray-400">هیچ مرحله‌ای تعریف نشده است.</p>
                </div>
            @else
                <div class="relative border-r-2 border-gray-200 dark:border-gray-700 mr-4 space-y-8 py-2">
                    @foreach($workflow->stages as $stage)
                        <div class="relative pr-8">
                            {{-- Timeline Dot --}}
                            <div class="absolute -right-[9px] top-0 w-4 h-4 rounded-full border-2 border-white dark:border-gray-900 {{ $stage->is_initial ? 'bg-emerald-500' : ($stage->is_final ? 'bg-gray-500' : 'bg-indigo-500') }}"></div>

                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                                <div class="bg-gray-50 dark:bg-gray-700/30 px-5 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                    <h3 class="font-bold text-gray-900 dark:text-white text-sm">
                                        {{ $stage->name }}
                                        @if($stage->is_initial)
                                            <span class="mr-2 text-[10px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full dark:bg-emerald-900/30 dark:text-emerald-400">شروع</span>
                                        @endif
                                    </h3>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-mono">#{{ $stage->sort_order }}</span>
                                </div>

                                <div class="p-5">
                                    @if($stage->actions->isEmpty())
                                        <p class="text-xs text-gray-400 italic">بدون عملیات.</p>
                                    @else
                                        <ul class="space-y-3">
                                            @foreach($stage->actions as $action)
                                                <li class="flex items-start gap-3 text-sm">
                                                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-[10px] font-bold dark:bg-indigo-900/30 dark:text-indigo-300 mt-0.5">
                                                        {{ $loop->iteration }}
                                                    </span>
                                                    <div>
                                                        <span class="font-medium text-gray-800 dark:text-gray-200">
                                                            @if($action->action_type === 'SEND_SMS') ارسال پیامک
                                                            @elseif($action->action_type === 'CREATE_TASK') ایجاد وظیفه
                                                            @else {{ $action->action_type }}
                                                            @endif
                                                        </span>
                                                        @if(!empty($action->config['title']))
                                                            <span class="text-gray-500 dark:text-gray-400"> - {{ $action->config['title'] }}</span>
                                                        @endif

                                                        @if(!empty($action->config))
                                                            <div class="mt-1 text-xs text-gray-400 dark:text-gray-500 font-mono bg-gray-50 dark:bg-gray-900/50 p-1.5 rounded border border-gray-100 dark:border-gray-700/50">
                                                                {{ json_encode($action->config, JSON_UNESCAPED_UNICODE) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
