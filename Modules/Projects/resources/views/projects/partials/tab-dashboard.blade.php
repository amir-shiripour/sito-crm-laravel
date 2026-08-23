@php
    use Modules\Projects\App\Http\Models\ProjectRole;$faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };
    $allProjectRoles = ProjectRole::all()->keyBy('name');
@endphp

<div class="space-y-6">

    {{-- KPI Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        {{-- 1. Project Progress Card (Based on Checklists / Subtasks) --}}
        <div
            class="bg-white dark:bg-gray-800/90 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-xs hover:shadow-lg hover:border-indigo-100 dark:hover:border-indigo-900/30 transition-all duration-300 flex flex-col justify-between group">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                    <span class="text-xs font-extrabold text-gray-500 dark:text-gray-400">پیشرفت کلی پروژه</span>
                </div>
                <span
                    class="w-11 h-11 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-sm shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </span>
            </div>
            <div class="mt-5 space-y-3">
                <div class="flex items-baseline justify-between">
                    <span id="dashboard-project-progress-text"
                          class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tight">{{ $faNum($dashboardStats['progress']) }}٪</span>
                    <span id="dashboard-tasks-ratio-text"
                          class="text-xs font-bold text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/40 px-2.5 py-1 rounded-xl border border-gray-100 dark:border-gray-700/60">
                        @if($dashboardStats['checklist_active'] > 0)
                            {{ $faNum($dashboardStats['checklist_done']) }}
                            از {{ $faNum($dashboardStats['checklist_active']) }} کار
                        @elseif($dashboardStats['checklist_total'] > 0)
                            تمام کارها لغو شده
                        @else
                            بدون کار
                        @endif
                    </span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700/60 h-2.5 rounded-full overflow-hidden p-0.5">
                    <div id="dashboard-project-progress-bar"
                         class="bg-indigo-600 h-full rounded-full transition-all duration-700 shadow-xs"
                         style="width: {{ $dashboardStats['progress'] }}%"></div>
                </div>
            </div>
        </div>

        {{-- 2. Work Items (Checklists) Stats Card --}}
        <div
            class="bg-white dark:bg-gray-800/90 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-xs hover:shadow-lg hover:border-emerald-100 dark:hover:border-emerald-900/30 transition-all duration-300 flex flex-col justify-between group">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full"
                          style="background-color: {{ $dashboardStats['status_colors']['checklist_completed'] ?? '#10b981' }}"></span>
                    <span class="text-xs font-extrabold text-gray-500 dark:text-gray-400">وضعیت کارها</span>
                </div>
                <span
                    class="w-11 h-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-sm shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-5 space-y-2">
                <div
                    class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tight flex items-baseline gap-1.5">
                    <span id="dashboard-checklist-done">{{ $faNum($dashboardStats['checklist_done']) }}</span>
                    <span class="text-lg font-bold text-gray-400">/ <span
                            id="dashboard-checklist-total">{{ $faNum($dashboardStats['checklist_active']) }}</span></span>
                    <span class="text-xs font-bold text-gray-400 mr-1">کار</span>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-1.5 pt-1">
                    <p id="dashboard-checklist-pct-text" class="text-xs text-gray-500 dark:text-gray-400 font-bold">
                        @if($dashboardStats['checklist_active'] > 0)
                            {{ $faNum($dashboardStats['checklist_progress']) }}٪ انجام شده
                        @elseif($dashboardStats['checklist_total'] > 0)
                            تمام کارها لغو شده
                        @else
                            بدون کار تعریف شده
                        @endif
                    </p>
                    <div class="flex items-center gap-1.5 text-[11px] font-bold">
                        @php
                            $chkDelayedColor = $dashboardStats['status_colors']['checklist_delayed'] ?? '#f97316';
                            $chkCanceledColor = $dashboardStats['status_colors']['checklist_canceled'] ?? '#ef4444';
                        @endphp
                        <span id="dashboard-checklist-overdue"
                              class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg font-bold {{ $dashboardStats['checklist_overdue'] > 0 ? '' : 'hidden' }}"
                              style="background-color: {{ $chkDelayedColor }}1a; color: {{ $chkDelayedColor }}; border: 1px solid {{ $chkDelayedColor }}33;">
                            {{ $faNum($dashboardStats['checklist_overdue']) }} تعویق
                        </span>
                        <span id="dashboard-checklist-canceled"
                              class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg font-bold {{ $dashboardStats['checklist_canceled'] > 0 ? '' : 'hidden' }}"
                              style="background-color: {{ $chkCanceledColor }}1a; color: {{ $chkCanceledColor }}; border: 1px solid {{ $chkCanceledColor }}33;">
                            {{ $faNum($dashboardStats['checklist_canceled']) }} لغو
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Groups Overview Card --}}
        <div
            class="bg-white dark:bg-gray-800/90 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-xs hover:shadow-lg hover:border-blue-100 dark:hover:border-blue-900/30 transition-all duration-300 flex flex-col justify-between group">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span class="text-xs font-extrabold text-gray-500 dark:text-gray-400">وضعیت گروه‌ها</span>
                </div>
                <span
                    class="w-11 h-11 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-sm shadow-blue-500/20 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </span>
            </div>
            <div class="mt-5 space-y-2">
                <div id="dashboard-total-tasks"
                     class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tight flex items-baseline gap-1.5">
                    <span id="dashboard-total-tasks-val">{{ $faNum($dashboardStats['total_tasks']) }}</span>
                    <span class="text-xs font-bold text-gray-400">گروه</span>
                </div>
                <div class="flex flex-wrap items-center gap-1.5 pt-1 text-xs font-bold">
                    @php
                        $tskCompletedColor = $dashboardStats['status_colors']['task_completed'] ?? '#10b981';
                        $tskDelayedColor = $dashboardStats['status_colors']['task_delayed'] ?? '#f97316';
                        $tskCanceledColor = $dashboardStats['status_colors']['task_canceled'] ?? '#ef4444';
                    @endphp
                    <span id="dashboard-completed-tasks"
                          class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg font-bold"
                          style="background-color: {{ $tskCompletedColor }}1a; color: {{ $tskCompletedColor }}; border: 1px solid {{ $tskCompletedColor }}33;">
                        {{ $faNum($dashboardStats['completed_tasks']) }} تکمیل
                    </span>
                    <span id="dashboard-overdue-tasks"
                          class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg font-bold {{ $dashboardStats['overdue_tasks'] > 0 ? '' : 'hidden' }}"
                          style="background-color: {{ $tskDelayedColor }}1a; color: {{ $tskDelayedColor }}; border: 1px solid {{ $tskDelayedColor }}33;">
                        {{ $faNum($dashboardStats['overdue_tasks']) }} تعویق
                    </span>
                    <span id="dashboard-canceled-tasks"
                          class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg font-bold {{ $dashboardStats['canceled_tasks'] > 0 ? '' : 'hidden' }}"
                          style="background-color: {{ $tskCanceledColor }}1a; color: {{ $tskCanceledColor }}; border: 1px solid {{ $tskCanceledColor }}33;">
                        {{ $faNum($dashboardStats['canceled_tasks']) }} لغو
                    </span>
                </div>
            </div>
        </div>

        {{-- 4. Project Schedule & Status Card --}}
        <div
            class="bg-white dark:bg-gray-800/90 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-xs hover:shadow-lg hover:border-purple-100 dark:hover:border-purple-900/30 transition-all duration-300 flex flex-col justify-between group">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    <span class="text-xs font-extrabold text-gray-500 dark:text-gray-400">مهلت و زمان‌بندی</span>
                </div>
                @php
                    $prjCanceledColor = $dashboardStats['status_colors']['project_canceled'] ?? '#ef4444';
                    $prjCompletedColor = $dashboardStats['status_colors']['project_completed'] ?? '#10b981';
                    $prjDelayedColor = $dashboardStats['status_colors']['project_delayed'] ?? '#f97316';
                    $scheduleIconBgStyle = 'background-color: #4f46e5; color: #ffffff;';
                    if ($dashboardStats['is_canceled']) {
                        $scheduleIconBgStyle = "background-color: {$prjCanceledColor}; color: #ffffff;";
                    } elseif ($dashboardStats['is_completed']) {
                        $scheduleIconBgStyle = "background-color: {$prjCompletedColor}; color: #ffffff;";
                    } elseif ($dashboardStats['is_overdue'] || ($dashboardStats['days_remaining'] ?? 0) < 0) {
                        $scheduleIconBgStyle = "background-color: {$prjDelayedColor}; color: #ffffff;";
                    } elseif (($dashboardStats['days_remaining'] ?? null) === 0) {
                        $scheduleIconBgStyle = "background-color: {$prjDelayedColor}; color: #ffffff;";
                    }
                @endphp
                <span id="dashboard-schedule-icon-wrap"
                      class="w-11 h-11 rounded-2xl flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform"
                      style="{{ $scheduleIconBgStyle }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-5 space-y-1.5" id="dashboard-schedule-container">
                @if($dashboardStats['is_canceled'])
                    <div id="dashboard-schedule-title" class="text-2xl sm:text-3xl font-black tracking-tight"
                         style="color: {{ $prjCanceledColor }}">لغو شده
                    </div>
                    <p id="dashboard-schedule-subtitle" class="text-xs font-bold"
                       style="color: {{ $prjCanceledColor }}cc">این پروژه لغو شده است</p>
                @elseif($dashboardStats['is_completed'])
                    <div id="dashboard-schedule-title" class="text-2xl sm:text-3xl font-black tracking-tight"
                         style="color: {{ $prjCompletedColor }}">تکمیل شده
                    </div>
                    <p id="dashboard-schedule-subtitle" class="text-xs font-bold"
                       style="color: {{ $prjCompletedColor }}cc">پروژه با موفقیت به پایان رسید</p>
                @elseif(!is_null($dashboardStats['days_remaining']))
                    @if($dashboardStats['days_remaining'] < 0 || $dashboardStats['is_overdue'])
                        <div id="dashboard-schedule-title"
                             class="text-2xl sm:text-3xl font-black tracking-tight"
                             style="color: {{ $prjDelayedColor }}">
                            {{ $faNum(abs($dashboardStats['days_remaining'])) }} روز گذشته
                        </div>
                        <p id="dashboard-schedule-subtitle" class="text-xs font-bold"
                           style="color: {{ $prjDelayedColor }}cc">از تاریخ سررسید پروژه گذشته است</p>
                    @elseif($dashboardStats['days_remaining'] == 0)
                        <div id="dashboard-schedule-title" class="text-2xl sm:text-3xl font-black tracking-tight"
                             style="color: {{ $prjDelayedColor }}">امروز سررسید
                        </div>
                        <p id="dashboard-schedule-subtitle" class="text-xs font-bold"
                           style="color: {{ $prjDelayedColor }}cc">امروز آخرین روز مهلت پروژه است</p>
                    @else
                        <div id="dashboard-schedule-title"
                             class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">{{ $faNum($dashboardStats['days_remaining']) }}
                            روز باقی‌مانده
                        </div>
                        <p id="dashboard-schedule-subtitle" class="text-xs text-gray-400 font-bold">تا تاریخ سررسید
                            تعیین شده</p>
                    @endif
                @else
                    <div id="dashboard-schedule-title" class="text-xl font-bold text-gray-400">نامشخص</div>
                    <p id="dashboard-schedule-subtitle" class="text-xs text-gray-400 font-bold">تاریخ پایان تعیین نشده
                        است</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Charts Section: Gantt Chart & Kanban Chart --}}
    @php
        $ganttTasks = $dashboardStats['gantt_tasks'] ?? collect();
        $kanbanColumns = $dashboardStats['kanban_columns'] ?? collect();
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div
            class="bg-white dark:bg-gray-800/80 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                    نمودار گانت زمان‌بندی گروه‌ها (Gantt Chart)
                </h3>
                <span class="text-xs text-gray-400 font-bold">بازه زمانی برحسب روزهای پروژه</span>
            </div>
            <div class="h-72 flex items-center justify-center relative">
                <div id="gantt-empty-state"
                     class="text-center text-gray-400 text-xs py-8 {{ $project->tasks->isEmpty() ? '' : 'hidden' }}">
                    <div
                        class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    هنوز گروهی برای رسم نمودار گانت ثبت نشده است.
                </div>
                <div id="gantt-canvas-container" class="w-full h-full {{ $project->tasks->isEmpty() ? 'hidden' : '' }}">
                    <canvas id="ganttChartCanvas"></canvas>
                </div>
            </div>
        </div>
        <div
            class="bg-white dark:bg-gray-800/80 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-600"></span>
                    توزیع گروه‌ها در ستون‌های کانبان
                </h3>
                <span class="text-xs text-gray-400 font-bold">توزیع گروه‌ها در مراحل کاری</span>
            </div>
            <div class="h-72 flex items-center justify-center relative">
                <div id="kanban-empty-state"
                     class="text-center text-gray-400 text-xs py-8 {{ $project->tasks->isEmpty() ? '' : 'hidden' }}">
                    <div
                        class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                    </div>
                    هنوز گروهی برای رسم نمودار کانبان ثبت نشده است.
                </div>
                <div id="kanban-canvas-container"
                     class="w-full h-full {{ $project->tasks->isEmpty() ? 'hidden' : '' }}">
                    <canvas id="kanbanChartCanvas"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Project Details & Team Members Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Project & Client Info (2 cols) --}}
        <div
            class="lg:col-span-2 bg-white dark:bg-gray-800/80 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm space-y-6">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                اطلاعات و مشخصات پروژه
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div
                    class="p-3.5 bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <span class="text-gray-400 block mb-1">مشتری</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200 text-sm">
                        {{ $project->client?->full_name ?? 'تعریف نشده' }}
                    </span>
                    @if($project->client?->phone)
                        <span class="text-gray-400 block mt-0.5 font-mono">{{ $project->client->phone }}</span>
                    @endif
                </div>
                <div
                    class="p-3.5 bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <span class="text-gray-400 block mb-1">دسته‌بندی</span>
                    @if($project->category)
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold"
                              style="background: {{ $project->category->color }}15; color: {{ $project->category->color }}; border: 1px solid {{ $project->category->color }}30">
                            {{ $project->category->name }}
                        </span>
                    @else
                        <span class="font-bold text-gray-800 dark:text-gray-200">تعریف نشده</span>
                    @endif
                </div>

                <div
                    class="p-3.5 bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <span class="text-gray-400 block mb-1">تاریخ شروع</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200">
                        {{ $project->start_date ? (function_exists('jdate') ? $faNum(jdate($project->start_date)->format('Y/m/d')) : $project->start_date->format('Y/m/d')) : 'نامشخص' }}
                    </span>
                </div>

                <div
                    class="p-3.5 bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <span class="text-gray-400 block mb-1">تاریخ پایان</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200">
                        {{ $project->end_date ? (function_exists('jdate') ? $faNum(jdate($project->end_date)->format('Y/m/d')) : $project->end_date->format('Y/m/d')) : 'نامشخص' }}
                    </span>
                </div>
            </div>

            @if($project->description)
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700/50">
                    <span class="text-xs font-bold text-gray-400 block mb-2">توضیحات و اهداف:</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line bg-gray-50 dark:bg-gray-900/30 p-4 rounded-2xl border border-gray-100 dark:border-gray-800">
                        {{ $project->description }}
                    </p>
                </div>
            @endif
        </div>

        {{-- Team Members List (1 col) --}}
        <div
            class="bg-white dark:bg-gray-800/80 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    تیم پروژه ({{ $faNum($project->members->count()) }})
                </h3>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                @forelse($project->members as $member)
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-xs font-black shrink-0">
                                {{ mb_substr($member->user?->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <div
                                    class="font-bold text-gray-900 dark:text-white text-xs">{{ $member->user?->name }}</div>
                                <div class="text-[11px] text-gray-400">{{ $member->user?->email }}</div>
                            </div>
                        </div>
                        @php
                            $roleObj = $allProjectRoles[$member->role] ?? null;
                            $roleName = $roleObj?->display_name ?? ($member->role === 'manager' ? 'مدیر پروژه' : ($member->role === 'editor' ? 'ویرایشگر' : 'مشاهده‌گر'));
                            $roleBadgeClass = $roleObj ? ($roleObj->colorClasses()['badge'] ?? 'bg-gray-100 text-gray-700') : 'bg-gray-100 text-gray-600';
                        @endphp
                        <span
                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold shrink-0 border {{ $roleBadgeClass }}">
                            {{ $roleName }}
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-center text-gray-400 text-xs">
                        عضوی برای این پروژه اختصاص داده نشده است.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        window.ganttChartInstance = null;
        window.kanbanChartInstance = null;
        window.currentGanttData = {!! json_encode($ganttTasks) !!};
        window.currentKanbanData = {!! json_encode($kanbanColumns) !!};

        function toFaDigits(n) {
            if (n === null || n === undefined) return '';
            const fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            return String(n).replace(/[0-9]/g, d => fa[d]);
        }

        function initProjectCharts() {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#9ca3af' : '#4b5563';
            const textHeadColor = isDark ? '#e5e7eb' : '#1f2937';
            const gridColor = isDark ? '#374151' : '#f3f4f6';

            // 1. Gantt Chart
            const ctxGantt = document.getElementById('ganttChartCanvas');
            if (ctxGantt && window.Chart && window.currentGanttData && window.currentGanttData.length > 0) {
                const ganttData = window.currentGanttData;
                const ganttLabels = ganttData.map(t => t.title.length > 22 ? t.title.substring(0, 22) + '...' : t.title);
                const ganttRanges = ganttData.map(t => [t.start_day, t.end_day]);
                const ganttBg = ganttData.map(t => t.status_color + 'c0');
                const ganttBorders = ganttData.map(t => t.status_color);
                const maxDay = Math.max(...ganttData.map(t => t.end_day), 5);

                if (window.ganttChartInstance) {
                    window.ganttChartInstance.destroy();
                }

                window.ganttChartInstance = new Chart(ctxGantt, {
                    type: 'bar',
                    data: {
                        labels: ganttLabels,
                        datasets: [{
                            label: 'بازه زمانی کار (روزها)',
                            data: ganttRanges,
                            backgroundColor: ganttBg,
                            borderColor: ganttBorders,
                            borderWidth: 1.5,
                            borderRadius: 8,
                            borderSkipped: false,
                            barPercentage: 0.65,
                            categoryPercentage: 0.85
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {left: 10, right: 15, top: 10, bottom: 5}
                        },
                        scales: {
                            x: {
                                position: 'top',
                                min: 0,
                                max: maxDay + 2,
                                grid: {color: gridColor},
                                ticks: {
                                    font: {family: 'inherit', size: 11},
                                    color: textColor,
                                    callback: function (v) {
                                        return 'روز ' + toFaDigits(v);
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'محور زمان (تعداد روز از شروع پروژه)',
                                    font: {family: 'inherit', size: 11, weight: 'bold'},
                                    color: textColor
                                }
                            },
                            y: {
                                grid: {display: false},
                                ticks: {
                                    font: {family: 'inherit', size: 11, weight: 'bold'},
                                    color: textHeadColor
                                }
                            }
                        },
                        plugins: {
                            legend: {display: false},
                            tooltip: {
                                rtl: true,
                                textDirection: 'rtl',
                                backgroundColor: isDark ? 'rgba(17, 24, 39, 0.95)' : 'rgba(255, 255, 255, 0.98)',
                                titleColor: isDark ? '#f9fafb' : '#111827',
                                bodyColor: isDark ? '#d1d5db' : '#374151',
                                borderColor: isDark ? '#374151' : '#e5e7eb',
                                borderWidth: 1,
                                padding: 12,
                                boxPadding: 6,
                                cornerRadius: 14,
                                titleFont: {family: 'inherit', size: 12, weight: 'bold'},
                                bodyFont: {family: 'inherit', size: 11, lineHeight: 1.6},
                                displayColors: false,
                                callbacks: {
                                    title: function (items) {
                                        const idx = items[0].dataIndex;
                                        const gData = window.currentGanttData || [];
                                        return gData[idx] ? gData[idx].title : '';
                                    },
                                    label: function (context) {
                                        const gData = window.currentGanttData || [];
                                        const task = gData[context.dataIndex];
                                        if (!task) return '';
                                        return [
                                            '• وضعیت: ' + task.status_name,
                                            '• مسئول کار: ' + task.assignee_name,
                                            '• تاریخ شروع: ' + task.start_date_str,
                                            '• مهلت تحویل: ' + task.due_date_str,
                                            '• درصد پیشرفت: ' + toFaDigits(task.progress) + '٪'
                                        ];
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 2. Kanban Chart
            const ctxKanban = document.getElementById('kanbanChartCanvas');
            if (ctxKanban && window.Chart && window.currentKanbanData && window.currentKanbanData.length > 0) {
                const kanbanData = window.currentKanbanData;
                const kanbanLabels = kanbanData.map(k => k.name);
                const kanbanCounts = kanbanData.map(k => k.count);
                const kanbanColors = kanbanData.map(k => k.color);

                if (window.kanbanChartInstance) {
                    window.kanbanChartInstance.destroy();
                }

                window.kanbanChartInstance = new Chart(ctxKanban, {
                    type: 'bar',
                    data: {
                        labels: kanbanLabels,
                        datasets: [{
                            label: 'تعداد وظایف',
                            data: kanbanCounts,
                            backgroundColor: kanbanColors.map(c => c + 'c0'),
                            borderColor: kanbanColors,
                            borderWidth: 2,
                            borderRadius: 12,
                            borderSkipped: false,
                            barThickness: 38
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {left: 10, right: 10, top: 15, bottom: 5}
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0,
                                    font: {family: 'inherit', size: 11},
                                    color: textColor,
                                    callback: function (v) {
                                        return toFaDigits(v) + ' کار';
                                    }
                                },
                                grid: {color: gridColor}
                            },
                            x: {
                                grid: {display: false},
                                ticks: {
                                    font: {family: 'inherit', size: 11, weight: 'bold'},
                                    color: textHeadColor
                                }
                            }
                        },
                        plugins: {
                            legend: {display: false},
                            tooltip: {
                                rtl: true,
                                textDirection: 'rtl',
                                backgroundColor: isDark ? 'rgba(17, 24, 39, 0.95)' : 'rgba(255, 255, 255, 0.98)',
                                titleColor: isDark ? '#f9fafb' : '#111827',
                                bodyColor: isDark ? '#d1d5db' : '#374151',
                                borderColor: isDark ? '#374151' : '#e5e7eb',
                                borderWidth: 1,
                                padding: 12,
                                boxPadding: 6,
                                cornerRadius: 14,
                                titleFont: {family: 'inherit', size: 12, weight: 'bold'},
                                bodyFont: {family: 'inherit', size: 11, lineHeight: 1.6},
                                displayColors: false,
                                callbacks: {
                                    title: function (items) {
                                        const idx = items[0].dataIndex;
                                        const kData = window.currentKanbanData || [];
                                        return 'مرحله: ' + (kData[idx] ? kData[idx].name : '');
                                    },
                                    label: function (context) {
                                        const kData = window.currentKanbanData || [];
                                        const col = kData[context.dataIndex];
                                        if (!col) return '';
                                        const total = kData.reduce((acc, c) => acc + c.count, 0);
                                        const pct = total > 0 ? Math.round((col.count / total) * 100) : 0;
                                        return [
                                            '• تعداد وظایف در این ستون: ' + toFaDigits(col.count) + ' کار',
                                            '• سهم از کل کارها: ' + toFaDigits(pct) + '٪',
                                            '• چک‌لیست‌های تکمیل شده: ' + toFaDigits(col.done_checklist) + ' از ' + toFaDigits(col.total_checklist)
                                        ];
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        window.updateProjectDashboard = function (stats) {
            if (!stats) return;

            // 1. Progress Card
            const progressText = document.getElementById('dashboard-project-progress-text');
            if (progressText) progressText.textContent = toFaDigits(stats.progress) + '٪';

            const tasksRatio = document.getElementById('dashboard-tasks-ratio-text');
            if (tasksRatio) {
                const activeChecklist = stats.checklist_active !== undefined ? stats.checklist_active : stats.checklist_total;
                if (activeChecklist > 0) {
                    tasksRatio.textContent = `${toFaDigits(stats.checklist_done)} از ${toFaDigits(activeChecklist)} کار`;
                } else if (stats.checklist_total > 0) {
                    tasksRatio.textContent = 'تمام کارها لغو شده';
                } else {
                    tasksRatio.textContent = 'بدون کار';
                }
            }

            const progressBar = document.getElementById('dashboard-project-progress-bar');
            if (progressBar) progressBar.style.width = stats.progress + '%';

            // Project Status Badge in Header
            if (stats.project_status) {
                const badge = document.getElementById('project-status-badge');
                const nameEl = document.getElementById('project-status-name');
                if (badge && nameEl) {
                    const color = stats.project_status.color || '#6366f1';
                    badge.style.backgroundColor = color + '1a';
                    badge.style.color = color;
                    badge.style.borderColor = color + '33';
                    const dot = badge.querySelector('span:first-child');
                    if (dot) dot.style.backgroundColor = color;
                    nameEl.textContent = stats.project_status.name;
                }
            }

            // Phases & Unphased Groups Progress Badges in Tasks tab
            if (stats.phases_stats && window.updatePhasesProgress) {
                window.updatePhasesProgress(stats.phases_stats);
            }

            const colors = stats.status_colors || {};
            const tskCompletedColor = colors.task_completed || '#10b981';
            const tskDelayedColor = colors.task_delayed || '#f97316';
            const tskCanceledColor = colors.task_canceled || '#ef4444';
            const chkDelayedColor = colors.checklist_delayed || '#f97316';
            const chkCanceledColor = colors.checklist_canceled || '#ef4444';
            const prjCompletedColor = colors.project_completed || '#10b981';
            const prjDelayedColor = colors.project_delayed || '#f97316';
            const prjCanceledColor = colors.project_canceled || '#ef4444';

            // 2. Groups Card
            const totalTasksEl = document.getElementById('dashboard-total-tasks-val');
            if (totalTasksEl) totalTasksEl.textContent = toFaDigits(stats.total_tasks);

            const completedTasksEl = document.getElementById('dashboard-completed-tasks');
            if (completedTasksEl) {
                completedTasksEl.textContent = `${toFaDigits(stats.completed_tasks)} تکمیل`;
                completedTasksEl.style.backgroundColor = tskCompletedColor + '1a';
                completedTasksEl.style.color = tskCompletedColor;
                completedTasksEl.style.borderColor = tskCompletedColor + '33';
            }

            const overdueTasksEl = document.getElementById('dashboard-overdue-tasks');
            if (overdueTasksEl) {
                if (stats.overdue_tasks > 0) {
                    overdueTasksEl.textContent = `${toFaDigits(stats.overdue_tasks)} تعویق`;
                    overdueTasksEl.style.backgroundColor = tskDelayedColor + '1a';
                    overdueTasksEl.style.color = tskDelayedColor;
                    overdueTasksEl.style.borderColor = tskDelayedColor + '33';
                    overdueTasksEl.classList.remove('hidden');
                } else {
                    overdueTasksEl.classList.add('hidden');
                }
            }

            const canceledTasksEl = document.getElementById('dashboard-canceled-tasks');
            if (canceledTasksEl) {
                if (stats.canceled_tasks > 0) {
                    canceledTasksEl.textContent = `${toFaDigits(stats.canceled_tasks)} لغو`;
                    canceledTasksEl.style.backgroundColor = tskCanceledColor + '1a';
                    canceledTasksEl.style.color = tskCanceledColor;
                    canceledTasksEl.style.borderColor = tskCanceledColor + '33';
                    canceledTasksEl.classList.remove('hidden');
                } else {
                    canceledTasksEl.classList.add('hidden');
                }
            }

            // 3. Work Items (Checklists) Card
            const chDoneEl = document.getElementById('dashboard-checklist-done');
            if (chDoneEl) chDoneEl.textContent = toFaDigits(stats.checklist_done);

            const chTotalEl = document.getElementById('dashboard-checklist-total');
            if (chTotalEl) {
                const activeChecklist = stats.checklist_active !== undefined ? stats.checklist_active : stats.checklist_total;
                chTotalEl.textContent = toFaDigits(activeChecklist);
            }

            const chPctEl = document.getElementById('dashboard-checklist-pct-text');
            if (chPctEl) {
                const activeChecklist = stats.checklist_active !== undefined ? stats.checklist_active : stats.checklist_total;
                if (activeChecklist > 0) {
                    const pct = stats.checklist_progress !== undefined ? stats.checklist_progress : Math.round((stats.checklist_done / activeChecklist) * 100);
                    chPctEl.textContent = `${toFaDigits(pct)}٪ انجام شده`;
                } else if (stats.checklist_total > 0) {
                    chPctEl.textContent = 'تمام کارها لغو شده';
                } else {
                    chPctEl.textContent = 'بدون کار تعریف شده';
                }
            }

            const chOverdueEl = document.getElementById('dashboard-checklist-overdue');
            if (chOverdueEl) {
                if (stats.checklist_overdue > 0) {
                    chOverdueEl.textContent = `${toFaDigits(stats.checklist_overdue)} تعویق`;
                    chOverdueEl.style.backgroundColor = chkDelayedColor + '1a';
                    chOverdueEl.style.color = chkDelayedColor;
                    chOverdueEl.style.borderColor = chkDelayedColor + '33';
                    chOverdueEl.classList.remove('hidden');
                } else {
                    chOverdueEl.classList.add('hidden');
                }
            }

            const chCanceledEl = document.getElementById('dashboard-checklist-canceled');
            if (chCanceledEl) {
                if (stats.checklist_canceled > 0) {
                    chCanceledEl.textContent = `${toFaDigits(stats.checklist_canceled)} لغو`;
                    chCanceledEl.style.backgroundColor = chkCanceledColor + '1a';
                    chCanceledEl.style.color = chkCanceledColor;
                    chCanceledEl.style.borderColor = chkCanceledColor + '33';
                    chCanceledEl.classList.remove('hidden');
                } else {
                    chCanceledEl.classList.add('hidden');
                }
            }

            // 4. Schedule Card
            const scheduleTitle = document.getElementById('dashboard-schedule-title');
            const scheduleSubtitle = document.getElementById('dashboard-schedule-subtitle');
            const scheduleIconWrap = document.getElementById('dashboard-schedule-icon-wrap');

            if (scheduleTitle && scheduleSubtitle) {
                if (stats.is_canceled) {
                    scheduleTitle.className = 'text-2xl sm:text-3xl font-black tracking-tight';
                    scheduleTitle.style.color = prjCanceledColor;
                    scheduleTitle.textContent = 'لغو شده';
                    scheduleSubtitle.className = 'text-xs font-bold';
                    scheduleSubtitle.style.color = prjCanceledColor + 'cc';
                    scheduleSubtitle.textContent = 'این پروژه لغو شده است';
                    if (scheduleIconWrap) {
                        scheduleIconWrap.style.backgroundColor = prjCanceledColor;
                        scheduleIconWrap.style.color = '#ffffff';
                    }
                } else if (stats.is_completed) {
                    scheduleTitle.className = 'text-2xl sm:text-3xl font-black tracking-tight';
                    scheduleTitle.style.color = prjCompletedColor;
                    scheduleTitle.textContent = 'تکمیل شده';
                    scheduleSubtitle.className = 'text-xs font-bold';
                    scheduleSubtitle.style.color = prjCompletedColor + 'cc';
                    scheduleSubtitle.textContent = 'پروژه با موفقیت به پایان رسید';
                    if (scheduleIconWrap) {
                        scheduleIconWrap.style.backgroundColor = prjCompletedColor;
                        scheduleIconWrap.style.color = '#ffffff';
                    }
                } else if (stats.days_remaining !== null && stats.days_remaining !== undefined) {
                    if (stats.days_remaining < 0 || stats.is_overdue) {
                        scheduleTitle.className = 'text-2xl sm:text-3xl font-black tracking-tight';
                        scheduleTitle.style.color = prjDelayedColor;
                        scheduleTitle.textContent = `${toFaDigits(Math.abs(stats.days_remaining))} روز گذشته`;
                        scheduleSubtitle.className = 'text-xs font-bold';
                        scheduleSubtitle.style.color = prjDelayedColor + 'cc';
                        scheduleSubtitle.textContent = 'از تاریخ سررسید پروژه گذشته است';
                        if (scheduleIconWrap) {
                            scheduleIconWrap.style.backgroundColor = prjDelayedColor;
                            scheduleIconWrap.style.color = '#ffffff';
                        }
                    } else if (stats.days_remaining === 0) {
                        scheduleTitle.className = 'text-2xl sm:text-3xl font-black tracking-tight';
                        scheduleTitle.style.color = prjDelayedColor;
                        scheduleTitle.textContent = 'امروز سررسید';
                        scheduleSubtitle.className = 'text-xs font-bold';
                        scheduleSubtitle.style.color = prjDelayedColor + 'cc';
                        scheduleSubtitle.textContent = 'امروز آخرین مهلت پروژه است';
                        if (scheduleIconWrap) {
                            scheduleIconWrap.style.backgroundColor = prjDelayedColor;
                            scheduleIconWrap.style.color = '#ffffff';
                        }
                    } else {
                        scheduleTitle.className = 'text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight';
                        scheduleTitle.style.color = '';
                        scheduleTitle.textContent = `${toFaDigits(stats.days_remaining)} روز باقی‌مانده`;
                        scheduleSubtitle.className = 'text-xs text-gray-400 font-bold';
                        scheduleSubtitle.style.color = '';
                        scheduleSubtitle.textContent = 'تا تاریخ سررسید تعیین شده';
                        if (scheduleIconWrap) {
                            scheduleIconWrap.style.backgroundColor = '#4f46e5';
                            scheduleIconWrap.style.color = '#ffffff';
                        }
                    }
                } else {
                    scheduleTitle.className = 'text-xl font-bold text-gray-400';
                    scheduleTitle.style.color = '';
                    scheduleTitle.textContent = 'نامشخص';
                    scheduleSubtitle.className = 'text-xs text-gray-400 font-bold';
                    scheduleSubtitle.style.color = '';
                    scheduleSubtitle.textContent = 'تاریخ پایان تعیین نشده است';
                    if (scheduleIconWrap) {
                        scheduleIconWrap.style.backgroundColor = '#6b7280';
                        scheduleIconWrap.style.color = '#ffffff';
                    }
                }
            }

            // 4. Update Gantt data and Chart
            if (stats.gantt_tasks) {
                window.currentGanttData = stats.gantt_tasks;
                const ganttEmpty = document.getElementById('gantt-empty-state');
                const ganttContainer = document.getElementById('gantt-canvas-container');
                if (stats.gantt_tasks.length === 0) {
                    if (ganttEmpty) ganttEmpty.classList.remove('hidden');
                    if (ganttContainer) ganttContainer.classList.add('hidden');
                } else {
                    if (ganttEmpty) ganttEmpty.classList.add('hidden');
                    if (ganttContainer) ganttContainer.classList.remove('hidden');
                    if (window.ganttChartInstance) {
                        const ganttLabels = stats.gantt_tasks.map(t => t.title.length > 22 ? t.title.substring(0, 22) + '...' : t.title);
                        const ganttRanges = stats.gantt_tasks.map(t => [t.start_day, t.end_day]);
                        const ganttBg = stats.gantt_tasks.map(t => t.status_color + 'c0');
                        const ganttBorders = stats.gantt_tasks.map(t => t.status_color);
                        const maxDay = Math.max(...stats.gantt_tasks.map(t => t.end_day), 5);

                        window.ganttChartInstance.data.labels = ganttLabels;
                        window.ganttChartInstance.data.datasets[0].data = ganttRanges;
                        window.ganttChartInstance.data.datasets[0].backgroundColor = ganttBg;
                        window.ganttChartInstance.data.datasets[0].borderColor = ganttBorders;
                        if (window.ganttChartInstance.options.scales?.x) {
                            window.ganttChartInstance.options.scales.x.max = maxDay + 2;
                        }
                        window.ganttChartInstance.update();
                    } else {
                        initProjectCharts();
                    }
                }
            }

            // 5. Update Kanban data and Chart
            if (stats.kanban_columns) {
                window.currentKanbanData = stats.kanban_columns;
                const kanbanEmpty = document.getElementById('kanban-empty-state');
                const kanbanContainer = document.getElementById('kanban-canvas-container');
                const totalTasks = stats.kanban_columns.reduce((a, b) => a + b.count, 0);
                if (totalTasks === 0) {
                    if (kanbanEmpty) kanbanEmpty.classList.remove('hidden');
                    if (kanbanContainer) kanbanContainer.classList.add('hidden');
                } else {
                    if (kanbanEmpty) kanbanEmpty.classList.add('hidden');
                    if (kanbanContainer) kanbanContainer.classList.remove('hidden');
                    if (window.kanbanChartInstance) {
                        const kanbanLabels = stats.kanban_columns.map(k => k.name);
                        const kanbanCounts = stats.kanban_columns.map(k => k.count);
                        const kanbanColors = stats.kanban_columns.map(k => k.color);

                        window.kanbanChartInstance.data.labels = kanbanLabels;
                        window.kanbanChartInstance.data.datasets[0].data = kanbanCounts;
                        window.kanbanChartInstance.data.datasets[0].backgroundColor = kanbanColors.map(c => c + 'c0');
                        window.kanbanChartInstance.data.datasets[0].borderColor = kanbanColors;
                        window.kanbanChartInstance.update();
                    } else {
                        initProjectCharts();
                    }
                }
            }
        };

        window.refreshProjectDashboard = function (projectId) {
            fetch(`{{ url('user/projects/projects') }}/${projectId}/dashboard-data`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
                .then(res => res.json())
                .then(stats => {
                    window.updateProjectDashboard(stats);
                })
                .catch(err => console.error('Failed to refresh dashboard stats:', err));
        };

        document.addEventListener('DOMContentLoaded', function () {
            initProjectCharts();

            // Resize on tab switch to dashboard
            window.addEventListener('tab-changed', function (e) {
                if (e.detail === 'dashboard') {
                    setTimeout(function () {
                        if (window.ganttChartInstance) window.ganttChartInstance.resize();
                        if (window.kanbanChartInstance) window.kanbanChartInstance.resize();
                    }, 50);
                }
            });
        });
    </script>
@endpush

