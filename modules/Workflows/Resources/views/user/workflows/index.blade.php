@extends('layouts.user')

@section('title', 'گردش کارها')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                    گردش کارها (Workflows)
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    تعریف و مشاهده جریان‌های کاری که روی وظایف (Tasks)، پیگیری‌ها (Follow-ups) و یادآوری‌ها (Reminders) سوار می‌شوند.
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <form method="get" class="flex items-center gap-2 w-full max-w-md">
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="جستجو بر اساس نام یا توضیحات گردش کار..."
                           class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                                  focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 transition-all duration-200
                                  dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900 dark:focus:border-emerald-500/50">
                </form>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($workflows as $wf)
                    <a href="{{ route('user.workflows.show', $wf) }}"
                       class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $wf->name }}
                                    </h3>
                                    @if($wf->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-700/20 dark:text-emerald-300">
                                            فعال
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-600 dark:bg-gray-700/60 dark:text-gray-300">
                                            غیرفعال
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                    {{ $wf->description }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                                    {{ $wf->stages_count }} مرحله
                                </span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400 text-center">
                        هنوز هیچ گردش کاری تعریف نشده است.
                    </div>
                @endforelse
            </div>

            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                {{ $workflows->links() }}
            </div>
        </div>
    </div>
@endsection
