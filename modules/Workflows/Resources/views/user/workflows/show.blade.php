@extends('layouts.user')

@section('title', 'جزئیات گردش کار')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    {{ $workflow->name }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $workflow->description }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                @if($workflow->is_active)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-700/20 dark:text-emerald-300">
                        فعال
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700/60 dark:text-gray-200">
                        غیرفعال
                    </span>
                @endif

                @can('workflows.manage')
                    <a href="{{ route('user.workflows.edit', $workflow) }}" class="inline-flex items-center px-3 py-1 text-xs font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">ویرایش</a>
                @endcan
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-4">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 13l4 4L19 7" />
                </svg>
                مراحل (Stages) و اکشن‌ها (Actions)
            </h2>

            @if($workflow->stages->isEmpty())
                <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-6">
                    هیچ مرحله‌ای برای این گردش کار تعریف نشده است.
                </div>
            @else
                <ol class="space-y-4">
                    @foreach($workflow->stages as $stage)
                        <li class="relative">
                            <div class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-3 h-3 rounded-full border-2 border-indigo-500 bg-white dark:bg-gray-900"></div>
                                    @if(!$loop->last)
                                        <div class="flex-1 w-px bg-gray-200 dark:bg-gray-700 mt-1"></div>
                                    @endif
                                </div>

                                <div class="flex-1 bg-gray-50 dark:bg-gray-900/40 rounded-xl px-4 py-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $stage->name }}
                                            </h3>
                                            @if($stage->is_initial)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-700/20 dark:text-emerald-300">
                                                    شروع (Initial)
                                                </span>
                                            @endif
                                            @if($stage->is_final)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-700/20 dark:text-indigo-300">
                                                    پایان (Final)
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                            ترتیب: {{ $stage->sort_order }}
                                        </span>
                                    </div>

                                    @if($stage->description)
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $stage->description }}
                                        </p>
                                    @endif

                                    <div class="mt-3 space-y-1.5">
                                        @forelse($stage->actions as $action)
                                            @php($cfg = $action->config ?? [])
                                            <div class="flex items-center justify-between text-xs bg-white dark:bg-gray-900/60 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-bold dark:bg-indigo-800/40 dark:text-indigo-200">
                                                        {{ $action->sort_order }}
                                                    </span>
                                                    <span class="font-medium text-gray-800 dark:text-gray-100">
                                                        {{ $action->action_type }}
                                                    </span>
                                                    @if(!empty($cfg['title']))
                                                        <span class="text-gray-500 dark:text-gray-400">
                                                            – {{ $cfg['title'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @if(!empty($cfg))
                                                    <span class="text-[11px] text-gray-400 dark:text-gray-500">
                                                        تنظیمات: {{ json_encode($cfg, JSON_UNESCAPED_UNICODE) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                                هیچ اکشنی برای این مرحله تعریف نشده است.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>
@endsection
