@php
    use Modules\Projects\App\Http\Models\ProjectSetting;$faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $user = auth()->user();
    $isSuperAdmin = $user && $user->hasAnyRole(['super-admin', 'superadmin']);
    $isManager = $project->isManager($user?->id) || ($project->created_by === $user?->id);

    // Dynamic Role-based Task Visibility Control
    $seeAssignedOnly = false;
    if (!$isSuperAdmin && !$isManager && $user) {
        $onlyShowAssignedSetting = ProjectSetting::getBool('projects_only_show_assigned_tasks_to_members', false);

        $seeAssignedOnly = $project->userHasPermission($user->id, 'tasks.see_assigned_only')
            || $onlyShowAssignedSetting;
    }

    $allProjectTasks = $project->tasks;
    if ($seeAssignedOnly && $user) {
        $allProjectTasks = $allProjectTasks->filter(function($task) use ($user) {
            return $task->assigned_to === $user->id
                || $task->manager_id === $user->id
                || $task->created_by === $user->id
                || $task->checklistItems->contains('assigned_to', $user->id);
        });
    }

    $phases = $project->phases;
    $unphasedTasks = $allProjectTasks->whereNull('phase_id')->whereNull('group_name');
    if ($unphasedTasks->isEmpty() && $allProjectTasks->whereNull('phase_id')->isNotEmpty()) {
        $unphasedTasks = $allProjectTasks->whereNull('phase_id');
    }
@endphp

<div class="space-y-6" x-data="tasksManager()">

    {{-- Top Action Bar --}}
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800/80 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm">
        <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                مدیریت فازها، گروه‌ها و کارها
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">فازبندی ساختاریافته پروژه با قابلیت جمع‌کردن بخش‌ها و مدیریت
                کارها.</p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            {{-- Toggle Expand/Collapse All Phases --}}
            @if($phases->count() > 1 || ($phases->count() >= 1 && $unphasedTasks->isNotEmpty()))
                <button type="button" @click="toggleAllGroups()"
                        class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs transition-all flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    <span x-text="allCollapsed ? 'باز کردن همه فازها' : 'جمع کردن همه فازها'"></span>
                </button>
            @endif

            {{-- Toggle Expand/Collapse All Groups --}}
            @if($allProjectTasks->count() > 1)
                <button type="button" @click="toggleAllTasks()"
                        class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs transition-all flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span x-text="allTasksCollapsed ? 'باز کردن همه گروه‌ها' : 'جمع کردن همه گروه‌ها'"></span>
                </button>
            @endif

            @can('managePhases', $project)
                {{-- Create Phase Button --}}
                <button type="button" @click="openCreatePhaseModal()"
                        class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-gray-700/60 dark:hover:bg-indigo-900/40 text-gray-700 dark:text-gray-200 font-bold text-xs border border-gray-200 dark:border-gray-600 transition-all flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    </svg>
                    + ایجاد فاز جدید
                </button>
            @endcan

            @can('applyTemplates', $project)
                {{-- Apply Template Button --}}
                <button type="button" @click="$dispatch('open-apply-template-modal')"
                        class="px-3.5 py-2.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 dark:hover:bg-purple-900/50 font-bold text-xs border border-purple-200/60 dark:border-purple-800/40 transition-all flex items-center gap-1.5 shadow-xs"
                        title="درون‌ریزی و اضافه کردن فازها و کارهای یک الگوی آماده به این پروژه">
                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    بارگذاری الگو
                </button>
            @endcan

            @can('manageTemplates', $project)
                @if($project->template)
                    {{-- Has Existing Template Button with Dropdown --}}
                    <div class="relative" x-data="{ openTemplateMenu: false }"
                         @click.outside="openTemplateMenu = false">
                        <div
                            class="inline-flex rounded-xl shadow-xs overflow-hidden border border-amber-300 dark:border-amber-700/60">
                            {{-- Main Action: Edit Template --}}
                            <a href="{{ route('projects.templates.edit', $project->template->id) }}"
                               class="px-3.5 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 dark:hover:bg-amber-900/50 font-bold text-xs transition-all flex items-center gap-1.5"
                               title="ویرایش الگوی مرتبط با این پروژه ({{ $project->template->title }})">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>ویرایش الگو</span>
                            </a>

                            {{-- Dropdown Toggle --}}
                            <button type="button" @click="openTemplateMenu = !openTemplateMenu"
                                    class="px-2 py-2.5 bg-amber-100 hover:bg-amber-200 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300 dark:hover:bg-amber-900 transition-all border-r border-amber-200/80 dark:border-amber-800/80 flex items-center justify-center"
                                    title="گزینه‌های الگو">
                                <svg class="w-3.5 h-3.5 transition-transform duration-200"
                                     :class="openTemplateMenu ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Dropdown Menu --}}
                        <div x-show="openTemplateMenu"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                             class="absolute left-0 mt-1.5 w-56 rounded-2xl bg-white dark:bg-gray-800 shadow-xl border border-gray-100 dark:border-gray-700 py-1.5 z-50 text-right">
                            <div
                                class="px-3.5 py-2 border-b border-gray-100 dark:border-gray-700 text-[11px] text-gray-500 dark:text-gray-400">
                                <span
                                    class="block font-bold text-gray-800 dark:text-gray-200 truncate">{{ $project->template->title }}</span>
                                <span class="text-[10px] text-amber-600 dark:text-amber-400 font-medium">الگوی ثبت‌شده از این پروژه</span>
                            </div>

                            <a href="{{ route('projects.templates.edit', $project->template->id) }}"
                               class="flex items-center gap-2 px-3.5 py-2 text-xs font-bold text-amber-800 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-900/20 hover:text-amber-700 transition-colors">
                                <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>ویرایش این الگو</span>
                            </a>

                            <a href="{{ route('projects.templates.show', $project->template->id) }}"
                               class="flex items-center gap-2 px-3.5 py-2 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:text-indigo-700 transition-colors">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span>مشاهده پیش‌نمایش الگو</span>
                            </a>

                            <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>

                            <a href="{{ route('projects.templates.create', ['from_project' => $project->id]) }}"
                               class="flex items-center gap-2 px-3.5 py-2 text-xs font-bold text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                                <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>ایجاد یک الگوی جدید دیگر</span>
                            </a>
                        </div>
                    </div>
                @else
                    {{-- Export / Save Structure As Template Button --}}
                    <a href="{{ route('projects.templates.create', ['from_project' => $project->id]) }}"
                       class="px-3.5 py-2.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 dark:hover:bg-amber-900/50 font-bold text-xs border border-amber-200/60 dark:border-amber-800/40 transition-all flex items-center gap-1.5 shadow-xs"
                       title="استخراج فازها و کارهای این پروژه به عنوان الگوی آماده">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                        ذخیره به عنوان الگو
                    </a>
                @endif
            @endcan

            @can('createTasks', $project)
                {{-- Create Task Button --}}
                <button type="button" @click="openCreateModal('')"
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    افزودن گروه جدید
                </button>
            @endcan
        </div>
    </div>

    {{-- Empty State (No phases and no tasks) --}}
    @if($project->tasks->isEmpty() && $phases->isEmpty())
        <div
            class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-100 dark:border-gray-700/50 p-12 text-center space-y-4 shadow-xs">
            <div
                class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto shadow-xs">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <h4 class="font-bold text-gray-900 dark:text-white text-base">پروژه هنوز فاز یا گروهی ندارد</h4>
            <p class="text-xs text-gray-400 max-w-md mx-auto leading-relaxed">
                می‌توانید با ایجاد فازهای مختلف (مانند تحلیل، طراحی، بک‌اند، تست) گروه‌های خود را به‌صورت کاملاً منظم و
                ساختاریافته مدیریت کنید.
            </p>
            @if($project->userHasPermission(auth()->id(), 'phases.manage') || $project->userHasPermission(auth()->id(), 'tasks.create') || $project->isManager(auth()->id()))
                <div class="flex items-center justify-center gap-3 pt-2">
                    @can('managePhases', $project)
                        <button type="button" @click="openCreatePhaseModal()"
                                class="px-4 py-2 rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 font-bold text-xs hover:bg-indigo-100 transition-all">
                            + ایجاد اولین فاز
                        </button>
                    @endcan
                    @can('createTasks', $project)
                        <button type="button" @click="openCreateModal()"
                                class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 transition-all">
                            + تعریف اولین گروه
                        </button>
                    @endcan
                </div>
            @endif
        </div>
    @else
        <div class="space-y-6">

            {{-- 1. Render Specific Project Phases --}}
            @foreach($phases as $phase)
                @php
                    $phaseSlug = 'phase_' . $phase->id;
                    $phaseTasks = $allProjectTasks->where('phase_id', $phase->id);
                    $phaseTotal = $phaseTasks->count();
                    $phaseCompleted = $phaseTasks->filter(fn($t) => $t->status?->isCompleted())->count();
                    $phasePct = $phaseTotal > 0 ? round(($phaseCompleted / $phaseTotal) * 100) : 0;
                @endphp

                <div
                    class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-200/80 dark:border-gray-700/60 shadow-xs overflow-hidden transition-all"
                    :class="(selectedPhases.includes({{ $phase->id }}) || selectedPhases.includes('{{ $phase->id }}')) ? 'ring-2 ring-indigo-500/50 border-indigo-400 dark:border-indigo-600' : ''"
                    id="task-group-box-{{ $phaseSlug }}">

                    {{-- Phase Header / Toggle Bar --}}
                    <div
                        class="p-4 sm:p-5 bg-gray-50/90 dark:bg-gray-900/50 border-b border-gray-200/70 dark:border-gray-700/60 flex items-center justify-between gap-4 cursor-pointer select-none"
                        @click="toggleGroup('{{ $phaseSlug }}')">

                        <div class="flex items-center gap-3 min-w-0">
                            @if(!$project->isCanceled() && ($project->userHasPermission(auth()->id(), 'phases.delete') || $project->isManager(auth()->id()) || $isSuperAdmin))
                                <div class="flex items-center shrink-0" @click.stop>
                                    <input type="checkbox"
                                           value="{{ $phase->id }}"
                                           x-model="selectedPhases"
                                           class="w-4 h-4 rounded-md text-indigo-600 focus:ring-2 focus:ring-indigo-500/20 border-gray-300 dark:border-gray-600 dark:bg-gray-700/80 cursor-pointer shadow-2xs transition-all"
                                           title="انتخاب فاز برای عملیات گروهی">
                                </div>
                            @endif

                            {{-- Collapse / Expand Chevron Icon --}}
                            <button type="button"
                                    class="p-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 transition-transform duration-200 shadow-xs"
                                    :class="collapsedGroups['{{ $phaseSlug }}'] ? 'rotate-180' : ''">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            {{-- Phase Color Indicator & Title --}}
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-xs"
                                      style="background-color: {{ $phase->color ?? '#6366f1' }}"></span>
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm sm:text-base truncate">
                                    {{ $phase->name }}
                                </h4>
                            </div>

                            {{-- Task Count & Progress Badge --}}
                            <span id="phase-progress-badge-{{ $phase->id }}"
                                  class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/40 shrink-0 mr-1">
                                {{ $faNum($phaseCompleted) }} از {{ $faNum($phaseTotal) }} تکمیل شده ({{ $faNum($phasePct) }}٪)
                            </span>
                        </div>

                        {{-- Phase Header Actions --}}
                        <div class="flex items-center gap-2 shrink-0" @click.stop>
                            @can('manageTasks', $project)
                                <button type="button"
                                        @click="openCreateModal({{ $phase->id }})"
                                        class="px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-300 text-gray-700 dark:text-gray-300 text-xs font-bold border border-gray-200 dark:border-gray-700 transition-all flex items-center gap-1.5 shadow-xs">
                                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                              d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span class="hidden sm:inline">افزودن گروه در این فاز</span>
                                </button>
                            @endcan

                            {{-- Edit Phase Button --}}
                            @can('managePhases', $project)
                                <button type="button"
                                        @click="openEditPhaseModal({{ $phase->id }}, '{{ addslashes($phase->name) }}', '{{ addslashes($phase->description ?? '') }}', '{{ $phase->color ?? '#6366f1' }}')"
                                        class="p-1.5 rounded-xl bg-white dark:bg-gray-800 text-gray-400 hover:text-amber-600 border border-gray-200 dark:border-gray-700 transition-all shadow-xs"
                                        title="ویرایش فاز">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                            @endcan

                            {{-- Delete Phase Button --}}
                            @can('deletePhases', $project)
                                <button type="button"
                                        onclick="deletePhase({{ $project->id }}, {{ $phase->id }}, '{{ addslashes($phase->name) }}')"
                                        class="p-1.5 rounded-xl bg-white dark:bg-gray-800 text-gray-400 hover:text-red-600 border border-gray-200 dark:border-gray-700 transition-all shadow-xs"
                                        title="حذف فاز">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            @endcan
                        </div>
                    </div>

                    {{-- Phase Body: Tasks List --}}
                    <div x-show="!collapsedGroups['{{ $phaseSlug }}']"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="p-4 sm:p-5 space-y-4">

                        @forelse($phaseTasks as $task)
                            @include('projects::projects.partials.task-card-item', ['task' => $task, 'project' => $project, 'taskStatuses' => $taskStatuses, 'faNum' => $faNum])
                        @empty
                            <div class="py-8 text-center text-gray-400 text-xs">
                                هنوز گروهی در این فاز ثبت نشده است.
                                @can('createTasks', $project)
                                    <button type="button" @click="openCreateModal({{ $phase->id }})"
                                            class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline mr-1">
                                        افزودن اولین گروه
                                    </button>
                                @endcan
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach

            {{-- 2. Render Unphased / General Tasks (if any exist) --}}
            @if($unphasedTasks->isNotEmpty())
                @php
                    $genSlug = 'phase_general';
                    $genTotal = $unphasedTasks->count();
                    $genCompleted = $unphasedTasks->filter(fn($t) => $t->status?->isCompleted())->count();
                    $genPct = $genTotal > 0 ? round(($genCompleted / $genTotal) * 100) : 0;
                @endphp

                <div
                    class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-200/80 dark:border-gray-700/60 shadow-xs overflow-hidden transition-all"
                    id="task-group-box-{{ $genSlug }}">

                    {{-- General Header --}}
                    <div
                        class="p-4 sm:p-5 bg-gray-50/90 dark:bg-gray-900/50 border-b border-gray-200/70 dark:border-gray-700/60 flex items-center justify-between gap-4 cursor-pointer select-none"
                        @click="toggleGroup('{{ $genSlug }}')">

                        <div class="flex items-center gap-3 min-w-0">
                            <button type="button"
                                    class="p-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 transition-transform duration-200 shadow-xs"
                                    :class="collapsedGroups['{{ $genSlug }}'] ? 'rotate-180' : ''">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div class="flex items-center gap-2.5 min-w-0">
                                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm sm:text-base truncate">
                                    گروه‌های عمومی (بدون فاز)
                                </h4>
                            </div>

                            <span id="phase-progress-badge-0"
                                  class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 shrink-0 mr-1">
                                {{ $faNum($genCompleted) }} از {{ $faNum($genTotal) }} تکمیل شده ({{ $faNum($genPct) }}٪)
                            </span>
                        </div>

                        <div class="flex items-center gap-2 shrink-0" @click.stop>
                            @can('createTasks', $project)
                                <button type="button"
                                        @click="openCreateModal('')"
                                        class="px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-300 text-gray-700 dark:text-gray-300 text-xs font-bold border border-gray-200 dark:border-gray-700 transition-all flex items-center gap-1.5 shadow-xs">
                                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                              d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span class="hidden sm:inline">افزودن گروه عمومی</span>
                                </button>
                            @endcan
                        </div>
                    </div>

                    {{-- General Tasks Body --}}
                    <div x-show="!collapsedGroups['{{ $genSlug }}']"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="p-4 sm:p-5 space-y-4">

                        @foreach($unphasedTasks as $task)
                            @include('projects::projects.partials.task-card-item', ['task' => $task, 'project' => $project, 'taskStatuses' => $taskStatuses, 'faNum' => $faNum])
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    @endif

    {{-- Create / Edit Task Modal --}}
    <div x-show="modalOpen" class="relative z-[9999]" role="dialog" aria-modal="true" style="display: none;">
        <div x-show="modalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="modalOpen = false"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20"
             @click.self="modalOpen = false; if(window.jalaliDatepicker) jalaliDatepicker.hide();">
            <div class="flex min-h-full items-end justify-center text-center sm:items-center"
                 @click.self="modalOpen = false; if(window.jalaliDatepicker) jalaliDatepicker.hide();">
                <div x-show="modalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100 dark:border-gray-700 p-6 space-y-5">

                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2"
                            x-text="isEdit ? 'ویرایش گروه' : 'تعریف گروه جدید'"></h3>
                        <button type="button" @click="modalOpen = false"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form :action="formAction" method="POST" class="space-y-4" @submit="validateTaskForm($event)">
                        @csrf
                        <input type="hidden" name="tab" value="tasks">
                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        {{-- Group Title --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">عنوان گروه
                                <span class="text-red-500">*</span></label>
                            <input type="text" name="title" x-model="form.title" required
                                   placeholder="مثال: تیم توسعه رابط کاربری"
                                   class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white">
                        </div>

                        {{-- Phase Selection (Locked if opened inside a phase, selectable if from top bar) --}}
                        <div>
                            <div x-show="isPhaseLocked && selectedPhaseName" class="space-y-1.5">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    فاز مربوطه
                                </label>
                                <div
                                    class="flex items-center justify-between p-3 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-800/50">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-3.5 h-3.5 rounded-full shrink-0"
                                              :style="`background-color: ${selectedPhaseColor || '#6366f1'}`"></span>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white"
                                              x-text="selectedPhaseName"></span>
                                    </div>
                                    <input type="hidden" name="phase_id" :value="form.phase_id">
                                    <button type="button" @click="isPhaseLocked = false"
                                            class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        تغییر فاز
                                    </button>
                                </div>
                            </div>

                            <div x-show="!isPhaseLocked || !selectedPhaseName">
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        انتخاب فاز / بخش پروژه
                                    </label>
                                    <button type="button" @click="modalOpen = false; openCreatePhaseModal();"
                                            class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        + تعریف فاز جدید
                                    </button>
                                </div>
                                <select name="phase_id" x-model="form.phase_id"
                                        class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3.5 py-2.5 text-xs font-bold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white cursor-pointer">
                                    <option value="">گروه‌های عمومی (بدون فاز اختصاصی)</option>
                                    @foreach($phases as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Assignee (Group Manager) --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    مدیر گروه <span class="text-red-500">*</span>
                                </label>
                                <select name="assigned_to" x-model="form.assigned_to" required
                                        class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white cursor-pointer">
                                    <option value="">انتخاب مدیر گروه (اجباری)</option>
                                    @foreach($project->members as $member)
                                        <option value="{{ $member->user_id }}">{{ $member->user?->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Due Date --}}
                            <div class="relative">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تاریخ
                                    سررسید</label>
                                @php
                                    $projectEndDateRaw = $project->end_date ? (function_exists('jdate') ? jdate($project->end_date)->format('Y/m/d') : $project->end_date->format('Y/m/d')) : '';
                                    $projectEndDateFormatted = $project->end_date && function_exists('jdate') ? $faNum(jdate($project->end_date)->format('Y/m/d')) : $projectEndDateRaw;
                                @endphp
                                <div class="relative flex items-center">
                                    <input type="text" data-jdp name="due_date" x-model="form.due_date"
                                           @if($projectEndDateRaw) data-jdp-max-date="{{ $projectEndDateRaw }}" @endif
                                           placeholder="۱۴۰۴/۰۱/۰۱" autocomplete="off" readonly
                                           @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({maxDate: 'attr'}); jalaliDatepicker.show($el); }"
                                           @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({maxDate: 'attr'}); jalaliDatepicker.show($el); }"
                                           @change="form.due_date = $event.target.value"
                                           @input="form.due_date = $event.target.value"
                                           class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white cursor-pointer">
                                    <button type="button" x-show="form.due_date" @click.stop="form.due_date = ''"
                                            class="absolute left-2.5 p-1 text-gray-400 hover:text-red-500 text-xs"
                                            title="پاک کردن تاریخ">
                                        ✕
                                    </button>
                                </div>
                                @if($project->end_date)
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 block">
                                        حداکثر تا پایان پروژه ({{ $projectEndDateFormatted }})
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">توضیحات
                                تکمیلی</label>
                            <textarea name="description" x-model="form.description" rows="2"
                                      placeholder="توضیحات در صورت نیاز..."
                                      class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white"></textarea>
                        </div>

                        {{-- Modal Footer --}}
                        <div
                            class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="modalOpen = false"
                                    class="px-5 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-200">
                                انصراف
                            </button>
                            <button type="submit"
                                    class="px-6 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold shadow-md hover:bg-indigo-700 transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-text="isEdit ? 'ذخیره تغییرات' : 'ایجاد گروه'"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- Create / Edit Phase Modal --}}
    <div x-show="phaseModalOpen" class="relative z-[9999]" role="dialog" aria-modal="true" style="display: none;">
        <div x-show="phaseModalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="phaseModalOpen = false"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20"
             @click.self="phaseModalOpen = false; if(window.jalaliDatepicker) jalaliDatepicker.hide();">
            <div class="flex min-h-full items-end justify-center text-center sm:items-center"
                 @click.self="phaseModalOpen = false; if(window.jalaliDatepicker) jalaliDatepicker.hide();">
                <div x-show="phaseModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 dark:border-gray-700 p-6 space-y-5">

                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                            </svg>
                            <span x-text="phaseIsEdit ? 'ویرایش فاز' : 'ایجاد فاز جدید'"></span>
                        </h3>
                        <button type="button" @click="phaseModalOpen = false"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form :action="phaseFormAction" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="tab" value="tasks">
                        <template x-if="phaseIsEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">عنوان فاز
                                <span class="text-red-500">*</span></label>
                            <input type="text" name="name" x-model="phaseForm.name" required
                                   placeholder="مثال: فاز اول: طراحی UI/UX"
                                   class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">رنگ شناسه
                                فاز</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="color" x-model="phaseForm.color"
                                       class="w-10 h-10 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer p-0.5 bg-transparent">
                                <span class="text-xs font-mono font-bold text-gray-600 dark:text-gray-300"
                                      x-text="phaseForm.color"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">توضیحات فاز
                                (اختیاری)</label>
                            <textarea name="description" x-model="phaseForm.description" rows="2"
                                      placeholder="اهداف یا دستاوردهای این فاز..."
                                      class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white"></textarea>
                        </div>

                        <div
                            class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="phaseModalOpen = false"
                                    class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-200">
                                انصراف
                            </button>
                            <button type="submit"
                                    class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-text="phaseIsEdit ? 'ذخیره تغییرات فاز' : 'ایجاد فاز'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Floating Bulk Actions Bar --}}
    <div x-show="totalSelectedCount > 0"
         x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-8 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-8 scale-95"
         class="fixed bottom-6 inset-x-0 mx-auto w-full max-w-lg z-50 px-4 pointer-events-auto">
        <div
            class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-gray-200/90 dark:border-gray-700/80 p-3.5 sm:p-4 rounded-2xl sm:rounded-3xl shadow-2xl shadow-gray-900/10 dark:shadow-black/50 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <span
                    class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl sm:rounded-2xl bg-indigo-50 dark:bg-indigo-900/50 border border-indigo-100 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-xs sm:text-sm shrink-0 shadow-2xs">
                    <span x-text="totalSelectedCount"></span>
                </span>
                <div class="min-w-0">
                    <p class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white truncate">
                        <span x-show="selectedPhases.length > 0" x-text="selectedPhases.length + ' فاز '"></span>
                        <span x-show="selectedPhases.length > 0 && selectedTasks.length > 0">و </span>
                        <span x-show="selectedTasks.length > 0" x-text="selectedTasks.length + ' گروه '"></span>
                        <span>انتخاب شده</span>
                    </p>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500">عملیات حذف گروهی روی موارد انتخابی</p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="deselectAll()"
                        class="px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700/70 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold transition-all shadow-2xs">
                    لغو
                </button>
                <button type="button" @click="deleteSelected()" :disabled="isBulkDeleting"
                        class="px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/50 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/60 text-xs font-bold transition-all shadow-2xs flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                    <svg x-show="isBulkDeleting" class="w-3.5 h-3.5 animate-spin text-rose-600 dark:text-rose-400"
                         fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <svg x-show="!isBulkDeleting" class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>حذف موارد</span>
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <style>
        jdp-container {
            z-index: 100000 !important;
        }
    </style>
    <script>
        function tasksManager() {
            return {
                modalOpen: false,
                isEdit: false,
                phaseModalOpen: false,
                phaseIsEdit: false,
                collapsedGroups: {},
                allCollapsed: false,
                collapsedTasks: {},
                allTasksCollapsed: false,
                selectedPhases: [],
                selectedTasks: [],
                isBulkDeleting: false,
                get totalSelectedCount() {
                    return this.selectedPhases.length + this.selectedTasks.length;
                },
                deselectAll() {
                    this.selectedPhases = [];
                    this.selectedTasks = [];
                },
                async deleteSelected() {
                    const phaseCount = this.selectedPhases.length;
                    const taskCount = this.selectedTasks.length;
                    if (phaseCount === 0 && taskCount === 0) return;

                    let msg = `آیا از حذف ${phaseCount > 0 ? phaseCount + ' فاز ' : ''}${phaseCount > 0 && taskCount > 0 ? 'و ' : ''}${taskCount > 0 ? taskCount + ' گروه ' : ''}انتخاب شده اطمینان دارید؟`;
                    if (phaseCount > 0) {
                        msg += '\n(با حذف فازها، تمام کارهای درون آن‌ها نیز حذف خواهند شد)';
                    }

                    if (!confirm(msg)) return;

                    this.isBulkDeleting = true;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                    try {
                        // 1. Delete selected phases if any
                        if (phaseCount > 0) {
                            const phaseRes = await fetch(`{{ route('projects.projects.phases.bulkDestroy', $project) }}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    phase_ids: this.selectedPhases.map(id => Number(id)),
                                    delete_tasks: true
                                })
                            });
                            const phaseData = await phaseRes.json();
                            if (!phaseRes.ok || !phaseData.success) {
                                throw new Error(phaseData.message || 'خطا در حذف فازها');
                            }
                        }

                        // 2. Delete selected tasks (excluding tasks that belonged to deleted phases)
                        if (taskCount > 0) {
                            const taskRes = await fetch(`{{ route('projects.projects.tasks.bulkDestroy', $project) }}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    task_ids: this.selectedTasks.map(id => Number(id))
                                })
                            });
                            const taskData = await taskRes.json();
                            if (!taskRes.ok || !taskData.success) {
                                throw new Error(taskData.message || 'خطا در حذف گروه‌ها');
                            }
                        }

                        window.location.reload();
                    } catch (err) {
                        console.error('Bulk delete error:', err);
                        alert(err.message || 'خطا در انجام عملیات حذف گروهی');
                        this.isBulkDeleting = false;
                    }
                },
                formAction: '{{ route("projects.projects.tasks.store", $project) }}',
                phasesData: @json($phases->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'color' => $p->color])),
                isPhaseLocked: false,
                get selectedPhaseName() {
                    if (!this.form.phase_id) return '';
                    const p = this.phasesData.find(ph => String(ph.id) === String(this.form.phase_id));
                    return p ? p.name : '';
                },
                get selectedPhaseColor() {
                    if (!this.form.phase_id) return '#6366f1';
                    const p = this.phasesData.find(ph => String(ph.id) === String(this.form.phase_id));
                    return p ? p.color : '#6366f1';
                },
                form: {
                    title: '',
                    phase_id: '',
                    status_id: '{{ $taskStatuses->first()?->id }}',
                    assigned_to: '',
                    due_date: '',
                    description: '',
                },
                phaseFormAction: '{{ route("projects.projects.phases.store", $project) }}',
                phaseForm: {
                    name: '',
                    color: '#6366f1',
                    description: '',
                },
                checklist: [],
                toggleGroup(slug) {
                    this.collapsedGroups[slug] = !this.collapsedGroups[slug];
                },
                toggleAllGroups() {
                    this.allCollapsed = !this.allCollapsed;
                    @foreach($phases as $p)
                        this.collapsedGroups['phase_{{ $p->id }}'] = this.allCollapsed;
                    @endforeach
                        this.collapsedGroups['phase_general'] = this.allCollapsed;
                },
                toggleTask(slug) {
                    this.collapsedTasks[slug] = !this.collapsedTasks[slug];
                },
                toggleAllTasks() {
                    this.allTasksCollapsed = !this.allTasksCollapsed;
                    @foreach($allProjectTasks as $t)
                        this.collapsedTasks['task_{{ $t->id }}'] = this.allTasksCollapsed;
                    @endforeach
                },
                openCreatePhaseModal() {
                    this.phaseIsEdit = false;
                    this.phaseFormAction = '{{ route("projects.projects.phases.store", $project) }}';
                    this.phaseForm = {
                        name: '',
                        color: '#6366f1',
                        description: '',
                    };
                    this.phaseModalOpen = true;
                },
                openEditPhaseModal(phaseId, name, description, color) {
                    this.phaseIsEdit = true;
                    this.phaseFormAction = '{{ url("user/projects/projects/{$project->id}/phases") }}/' + phaseId;
                    this.phaseForm = {
                        name: name,
                        color: color || '#6366f1',
                        description: description || '',
                    };
                    this.phaseModalOpen = true;
                },
                openCreateModal(presetPhaseId = '') {
                    this.isEdit = false;
                    this.isPhaseLocked = Boolean(presetPhaseId);
                    this.formAction = '{{ route("projects.projects.tasks.store", $project) }}';
                    this.form = {
                        title: '',
                        phase_id: presetPhaseId ? String(presetPhaseId) : '',
                        status_id: '{{ $taskStatuses->first()?->id }}',
                        assigned_to: '',
                        due_date: '',
                        description: '',
                    };
                    this.checklist = [];
                    this.modalOpen = true;
                    this.$nextTick(() => {
                        if (window.jalaliDatepicker) {
                            jalaliDatepicker.startWatch({
                                selector: '[data-jdp]',
                                minDate: 'attr',
                                maxDate: 'attr',
                                date: true,
                                time: false
                            });
                        }
                    });
                },
                openEditModal(task, items) {
                    this.isEdit = true;
                    this.isPhaseLocked = false;
                    this.formAction = '{{ url("user/projects/projects/{$project->id}/tasks") }}/' + task.id;
                    this.form = {
                        title: task.title,
                        phase_id: task.phase_id ? String(task.phase_id) : '',
                        status_id: task.status_id,
                        assigned_to: task.assigned_to || '',
                        due_date: task.due_date_jalali || '',
                        description: task.description || '',
                    };
                    this.checklist = (items || []).map(i => ({
                        id: i.id,
                        title: i.title,
                        is_done: !!i.is_done,
                        due_date: i.due_date_jalali || '',
                    }));
                    this.modalOpen = true;
                    this.$nextTick(() => {
                        if (window.jalaliDatepicker) {
                            jalaliDatepicker.startWatch({
                                selector: '[data-jdp]',
                                minDate: 'attr',
                                maxDate: 'attr',
                                date: true,
                                time: false
                            });
                        }
                    });
                },
                addChecklistItem() {
                    this.checklist.push({
                        id: null,
                        title: '',
                        is_done: false,
                        due_date: '',
                    });
                },
                removeChecklistItem(idx) {
                    this.checklist.splice(idx, 1);
                },
                validateTaskForm(e) {
                    if (!this.form.assigned_to) {
                        e.preventDefault();
                        alert('⚠️ انتخاب مدیر گروه الزامی است.');
                        return false;
                    }

                    const projectEndDate = '{{ $project->end_date ? (function_exists("jdate") ? jdate($project->end_date)->format("Y/m/d") : $project->end_date->format("Y/m/d")) : "" }}';
                    if (projectEndDate && this.form.due_date) {
                        const formDateClean = this.form.due_date.replace(/[^0-9]/g, '');
                        const projectDateClean = projectEndDate.replace(/[^0-9]/g, '');
                        if (formDateClean && projectDateClean && formDateClean > projectDateClean) {
                            e.preventDefault();
                            alert(`⚠️ تاریخ سررسید گروه نمی‌تواند پس از تاریخ پایان پروژه (${projectEndDate}) باشد.`);
                            return false;
                        }
                    }

                    if (this.checklist && this.checklist.length > 0) {
                        for (let i = 0; i < this.checklist.length; i++) {
                            const item = this.checklist[i];
                            if (item.due_date) {
                                const itemDateClean = item.due_date.replace(/[^0-9]/g, '');
                                if (this.form.due_date) {
                                    const groupDateClean = this.form.due_date.replace(/[^0-9]/g, '');
                                    if (itemDateClean && groupDateClean && itemDateClean > groupDateClean) {
                                        e.preventDefault();
                                        alert(`⚠️ تاریخ سررسید کار «${item.title || 'کار'}» نمی‌تواند پس از تاریخ سررسید گروه (${this.form.due_date}) باشد.`);
                                        return false;
                                    }
                                } else if (projectEndDate) {
                                    const projectDateClean = projectEndDate.replace(/[^0-9]/g, '');
                                    if (itemDateClean && projectDateClean && itemDateClean > projectDateClean) {
                                        e.preventDefault();
                                        alert(`⚠️ تاریخ سررسید کار «${item.title || 'کار'}» نمی‌تواند پس از تاریخ پایان پروژه (${projectEndDate}) باشد.`);
                                        return false;
                                    }
                                }
                            }
                        }
                    }

                    return true;
                }
            };
        }


        function deletePhase(projectId, phaseId, phaseName) {
            if (!confirm(`آیا از حذف فاز «${phaseName}» اطمینان دارید؟ تمام کارهای این فاز به وظایف عمومی منتقل خواهند شد.`)) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`{{ url('user/projects/projects') }}/${projectId}/phases/${phaseId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                })
                .catch(err => console.error('Delete phase error:', err));
        }


        window.updateTaskStatusBadgeAndIcon = function (taskId, status) {
            if (!status || !taskId) return;
            const color = status.color || '#6366f1';
            const name = status.name || '';

            // 1. Update Status Badge (Pill)
            const badge = document.getElementById('task-status-badge-' + taskId);
            if (badge) {
                badge.style.backgroundColor = color + '18';
                badge.style.color = color;
                badge.style.borderColor = color + '33';
                const dot = badge.querySelector('span:first-child');
                if (dot) dot.style.backgroundColor = color;
                const nameEl = document.getElementById('task-status-name-' + taskId);
                if (nameEl) nameEl.textContent = name;
            }

            // 2. Update Icon Box and Dot next to Group Title
            const iconBox = document.getElementById('task-icon-box-' + taskId);
            if (iconBox) {
                iconBox.style.background = color + '18';
                iconBox.style.borderColor = color + '33';
            }
            const iconDot = document.getElementById('task-icon-dot-' + taskId);
            if (iconDot) {
                iconDot.style.background = color;
            }
        };

        function cancelTaskGroup(projectId, taskId, taskTitle) {
            if (!confirm(`آیا از لغو گروه «${taskTitle}» اطمینان دارید؟\nاین عملیات غیرقابل بازگشت است و کارهای این گروه قفل خواهند شد.`)) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`{{ url('user/projects/projects') }}/${projectId}/tasks/${taskId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(data => {
                            throw new Error(data.message || 'خطا در لغو گروه');
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        // 1. Update task card badge & icon in Tasks tab
                        if (data.task?.status) {
                            window.updateTaskStatusBadgeAndIcon(taskId, data.task.status);
                        } else {
                            window.updateTaskStatusBadgeAndIcon(taskId, {color: '#ef4444', name: 'لغو شده'});
                        }

                        // 2. Hide cancel button for this task
                        const cancelBtn = document.getElementById('task-cancel-btn-' + taskId);
                        if (cancelBtn) cancelBtn.remove();

                        // 3. Remove group edit & delete buttons
                        const groupEditBtn = document.getElementById('task-edit-btn-' + taskId);
                        if (groupEditBtn) groupEditBtn.remove();
                        const groupDeleteForm = document.getElementById('task-delete-form-' + taskId);
                        if (groupDeleteForm) groupDeleteForm.remove();
                        const groupDeleteBtn = document.getElementById('task-delete-btn-' + taskId);
                        if (groupDeleteBtn) groupDeleteBtn.remove();

                        // 4. Remove all subtask action buttons (edit & delete) in this task card
                        document.querySelectorAll('.checklist-action-btn-' + taskId).forEach(btn => btn.remove());

                        // 5. Disable all checkboxes in this task & remove quick add container & comment forms
                        const taskCard = document.getElementById('task-card-' + taskId);
                        if (taskCard) {
                            taskCard.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                                cb.disabled = true;
                                cb.classList.add('opacity-50', 'cursor-not-allowed');
                            });
                            const quickAdd = document.getElementById('quick-add-container-' + taskId);
                            if (quickAdd) quickAdd.remove();
                            taskCard.querySelectorAll('[id^="item-comment-form-"]').forEach(cf => {
                                cf.outerHTML = '<div class="p-2 text-center text-[11px] text-gray-400 bg-white/60 dark:bg-gray-800/60 rounded-xl border border-dashed border-gray-200 dark:border-gray-700/60 font-medium" id="' + cf.id + '">امکان ثبت کامنت برای کارهای لغو شده وجود ندارد.</div>';
                            });
                        }

                        // 4. Move card in Kanban board
                        const kanbanCard = document.getElementById('kanban-task-' + taskId);
                        const targetStatusId = data.task?.status_id;
                        const targetZone = document.getElementById('kanban-drop-zone-' + targetStatusId);
                        if (kanbanCard && targetZone) {
                            const fromZone = kanbanCard.parentElement;
                            const placeholder = targetZone.querySelector('.empty-placeholder');
                            if (placeholder) placeholder.remove();

                            targetZone.appendChild(kanbanCard);
                            kanbanCard.setAttribute('data-status-id', targetStatusId);
                            kanbanCard.setAttribute('ondragstart', `handleKanbanDragStart(event, ${taskId}, ${targetStatusId})`);

                            if (fromZone && fromZone !== targetZone) {
                                const remaining = fromZone.querySelectorAll('[id^="kanban-task-"]');
                                if (remaining.length === 0 && !fromZone.querySelector('.empty-placeholder')) {
                                    const emptyDiv = document.createElement('div');
                                    emptyDiv.className = 'py-10 text-center text-gray-400 text-xs empty-placeholder';
                                    emptyDiv.textContent = 'کاری در این ستون نیست';
                                    fromZone.appendChild(emptyDiv);
                                }
                            }

                            if (typeof updateKanbanCounters === 'function') {
                                updateKanbanCounters();
                            }
                        }

                        // 5. Update Dashboard
                        if (data.dashboard_stats && window.updateProjectDashboard) {
                            window.updateProjectDashboard(data.dashboard_stats);
                        } else if (window.refreshProjectDashboard) {
                            window.refreshProjectDashboard(projectId);
                        }

                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {type: 'success', text: `گروه «${data.task?.title || ''}» با موفقیت لغو شد.`}
                        }));
                    }
                })
                .catch(err => {
                    alert(err.message);
                });
        }

        function updateTaskStatus(projectId, taskId, statusId, selectEl) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`{{ url('user/projects/projects') }}/${projectId}/tasks/${taskId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({status_id: statusId})
            })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(data => {
                            throw new Error(data.message || 'انتقال وضعیت مجاز نیست.');
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        const isCompleted = data.task?.completed_at || (data.task?.status && (data.task.status.is_completed || data.task.status.name === 'تکمیل شده'));
                        if (selectEl && data.task?.status?.color) {
                            selectEl.style.color = data.task.status.color;
                        }
                        if (data.task?.status) {
                            window.updateTaskStatusBadgeAndIcon(taskId, data.task.status);
                        }
                        const titleEl = document.getElementById('task-card-title-' + taskId);
                        if (titleEl) {
                            if (isCompleted) {
                                titleEl.classList.add('line-through', 'text-gray-400', 'dark:text-gray-500');
                            } else {
                                titleEl.classList.remove('line-through', 'text-gray-400', 'dark:text-gray-500');
                            }
                        }

                        // Move Kanban card if kanban element exists
                        const kanbanCard = document.getElementById('kanban-task-' + taskId);
                        const targetZone = document.getElementById('kanban-drop-zone-' + statusId);
                        if (kanbanCard && targetZone) {
                            const fromZone = kanbanCard.parentElement;
                            const placeholder = targetZone.querySelector('.empty-placeholder');
                            if (placeholder) placeholder.remove();

                            targetZone.appendChild(kanbanCard);
                            kanbanCard.setAttribute('data-status-id', statusId);
                            kanbanCard.setAttribute('ondragstart', `handleKanbanDragStart(event, ${taskId}, ${statusId})`);

                            const kanbanTitle = kanbanCard.querySelector('h5');
                            if (kanbanTitle) {
                                if (isCompleted) {
                                    kanbanTitle.classList.add('line-through', 'text-gray-400', 'dark:text-gray-500');
                                } else {
                                    kanbanTitle.classList.remove('line-through', 'text-gray-400', 'dark:text-gray-500');
                                }
                            }

                            if (fromZone && fromZone !== targetZone) {
                                const remaining = fromZone.querySelectorAll('[id^="kanban-task-"]');
                                if (remaining.length === 0 && !fromZone.querySelector('.empty-placeholder')) {
                                    const emptyDiv = document.createElement('div');
                                    emptyDiv.className = 'py-10 text-center text-gray-400 text-xs empty-placeholder';
                                    emptyDiv.textContent = 'کاری در این ستون نیست';
                                    fromZone.appendChild(emptyDiv);
                                }
                            }

                            if (typeof updateKanbanCounters === 'function') {
                                updateKanbanCounters();
                            }
                        }

                        // Sync checkboxes inside task card
                        const items = data.task?.checklist_items || data.task?.checklistItems || [];
                        if (items.length > 0) {
                            items.forEach(ci => {
                                const cb = document.getElementById('checklist-checkbox-' + ci.id);
                                if (cb) {
                                    cb.checked = !!ci.is_done;
                                }
                                const titleSpan = document.getElementById('checklist-item-title-' + ci.id);
                                if (titleSpan) {
                                    if (ci.is_done) {
                                        titleSpan.classList.add('line-through', 'text-gray-400', 'dark:text-gray-500');
                                    } else {
                                        titleSpan.classList.remove('line-through', 'text-gray-400', 'dark:text-gray-500');
                                    }
                                }
                            });
                        }
                        const taskCard = document.getElementById('task-card-' + taskId);
                        const isQueued = data.task?.status?.is_queued || (data.task?.status?.name && (data.task.status.name.includes('صف') || data.task.status.name.toLowerCase().includes('queue')));
                        if (isQueued && taskCard) {
                            taskCard.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                                cb.checked = false;
                            });
                            taskCard.querySelectorAll('[id^="checklist-item-title-"]').forEach(span => {
                                span.classList.remove('line-through', 'text-gray-400', 'dark:text-gray-500');
                            });
                        } else if (isCompleted && taskCard) {
                            taskCard.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                                cb.checked = true;
                            });
                            taskCard.querySelectorAll('[id^="checklist-item-title-"]').forEach(span => {
                                span.classList.add('line-through', 'text-gray-400', 'dark:text-gray-500');
                            });
                        }

                        if (typeof updateChecklistProgress === 'function') {
                            updateChecklistProgress(taskId, data.task_progress, data.checklist_done, data.checklist_total);
                        }

                        // Live update dashboard
                        if (data.dashboard_stats && window.updateProjectDashboard) {
                            window.updateProjectDashboard(data.dashboard_stats);
                        } else if (window.refreshProjectDashboard) {
                            window.refreshProjectDashboard(projectId);
                        }

                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {type: 'success', text: `وضعیت کار «${data.task?.title || ''}» به‌روز شد.`}
                        }));
                    }
                })
                .catch(err => {
                    alert(err.message);
                    if (selectEl) {
                        window.location.reload();
                    }
                });
        }

        function toggleChecklistItem(projectId, taskId, itemId, checkbox) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`{{ url('user/projects/projects') }}/${projectId}/tasks/${taskId}/checklist/${itemId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({is_done: checkbox.checked ? 1 : 0})
            })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(data => {
                            throw new Error(data.message || 'خطا در تغییر وضعیت کار');
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        window.applyChecklistToggleUpdate(data, true);
                    }
                })
                .catch(err => {
                    console.error('toggleChecklistItem error:', err);
                    alert(err.message || 'خطا در تغییر وضعیت کار');
                    checkbox.checked = !checkbox.checked;
                });
        }

        window.applyChecklistToggleUpdate = function (data, isLocalAction = false) {
            const itemId = data.item_id || data.item?.id;
            const taskId = data.task_id || data.task?.id;
            const currentUserId = {{ (int)auth()->id() }};

            if (!itemId) return;

            // 1. Checkbox state for current user
            const checkbox = document.getElementById('checklist-checkbox-' + itemId);
            if (checkbox) {
                if (data.item?.is_completed_by_me !== undefined && isLocalAction) {
                    checkbox.checked = !!data.item.is_completed_by_me;
                } else if (Array.isArray(data.item?.assignees) && data.item.assignees.length > 0) {
                    const myAssignee = data.item.assignees.find(a => String(a.id) === String(currentUserId));
                    if (myAssignee) {
                        checkbox.checked = !!myAssignee.is_done;
                    } else {
                        checkbox.checked = !!data.item.is_done;
                    }
                } else if (data.item?.is_done !== undefined) {
                    checkbox.checked = !!data.item.is_done;
                }
            }

            // 2. Title strikethrough (completed when total item is completed or canceled)
            const titleSpan = document.getElementById('checklist-item-title-' + itemId);
            if (titleSpan) {
                if (data.item?.is_done) {
                    titleSpan.classList.add('line-through', 'text-gray-400', 'dark:text-gray-500');
                } else if (!data.item?.is_canceled) {
                    titleSpan.classList.remove('line-through', 'text-gray-400', 'dark:text-gray-500');
                }
            }

            // 3. Update task progress bar & counters
            if (taskId && data.task_progress !== undefined) {
                updateChecklistProgress(taskId, data.task_progress, data.checklist_done, data.checklist_total);
            }

            // 4. Update checklist item status badge
            const itemStatus = data.item?.status;
            const isDone = data.item?.is_done;
            const isOverdue = data.item?.is_overdue;

            let statusName = '';
            let statusColor = '#6b7280';

            if (itemStatus) {
                statusName = itemStatus.name;
                statusColor = itemStatus.color || '#6b7280';
            } else if (isDone) {
                statusName = 'تکمیل شده';
                statusColor = '#10b981';
            } else if (isOverdue) {
                statusName = 'تعویق';
                statusColor = '#ef4444';
            }

            const itemBadge = document.getElementById('checklist-status-badge-' + itemId);
            if (itemBadge) {
                if (statusName) {
                    itemBadge.classList.remove('hidden');
                    itemBadge.style.backgroundColor = statusColor + '18';
                    itemBadge.style.color = statusColor;
                    itemBadge.style.borderColor = statusColor + '33';
                    const itemBadgeDot = itemBadge.querySelector('span:first-child');
                    if (itemBadgeDot) {
                        itemBadgeDot.style.backgroundColor = statusColor;
                    }
                    const itemBadgeName = document.getElementById('checklist-status-name-' + itemId);
                    if (itemBadgeName) {
                        itemBadgeName.textContent = statusName;
                    } else {
                        itemBadge.textContent = statusName;
                    }
                } else {
                    itemBadge.classList.add('hidden');
                }
            }

            // 5. Update Details Accordion
            const detailsText = document.getElementById('checklist-details-status-text-' + itemId);
            const detailsIcon = document.getElementById('checklist-details-status-icon-' + itemId);
            if (detailsText) {
                detailsText.textContent = statusName || '—';
                detailsText.style.color = statusColor;
            }
            if (detailsIcon) {
                detailsIcon.style.backgroundColor = statusColor + '18';
                detailsIcon.style.color = statusColor;
                const detailsDot = detailsIcon.querySelector('span');
                if (detailsDot) {
                    detailsDot.style.backgroundColor = statusColor;
                }
            }

            // 6. Update Multi-Assignees Badges & Details
            if (data.item?.assignees_total_count > 1) {
                const assigneesBadge = document.getElementById('checklist-assignees-badge-' + itemId);
                if (assigneesBadge) {
                    const isAllDone = data.item.is_done;
                    const doneCount = data.item.assignees_completed_count || 0;
                    const totalCount = data.item.assignees_total_count || 0;
                    assigneesBadge.innerHTML = `
                        <span class="inline-flex items-center gap-1 text-[10px] ${isAllDone ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/30 border-emerald-200/60 dark:border-emerald-800/40' : 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 border-indigo-100 dark:border-indigo-800/40'} px-1.5 py-0.5 rounded-md font-bold border">
                            <span>${totalCount} مسئول</span>
                            <span class="text-[9px] font-bold opacity-80">(${doneCount}/${totalCount})</span>
                        </span>
                    `;
                }

                const detailsAssignees = document.getElementById('checklist-details-assignees-' + itemId);
                if (detailsAssignees && Array.isArray(data.item.assignees)) {
                    let html = '<div class="flex flex-wrap items-center gap-1.5 mt-0.5">';
                    data.item.assignees.forEach(u => {
                        html += `
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded ${u.is_done ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'}">
                                <span>${u.is_done ? '✓' : '○'}</span>
                                <span>${u.name}</span>
                            </span>
                        `;
                    });
                    html += '</div>';
                    detailsAssignees.innerHTML = html;
                }
            }

            // 7. Due date badge
            const dueDateBadge = document.getElementById('checklist-duedate-badge-' + itemId);
            if (dueDateBadge) {
                dueDateBadge.className = 'hidden md:inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded-md border shrink-0 bg-gray-50 dark:bg-gray-700/40 text-gray-500 dark:text-gray-300 border-gray-200/60 dark:border-gray-600/40';
            }

            // 8. Real-time update Task Status Badge and Icon
            if (taskId && data.task?.status) {
                window.updateTaskStatusBadgeAndIcon(taskId, data.task.status);
            }

            // 9. Update Task Title strikethrough
            const taskTitle = document.getElementById('task-card-title-' + taskId);
            const isCompleted = data.task?.completed_at || (data.task?.status && (data.task.status.is_completed || data.task.status.name === 'تکمیل شده'));
            if (taskTitle) {
                if (isCompleted) {
                    taskTitle.classList.add('line-through', 'text-gray-400', 'dark:text-gray-500');
                } else {
                    taskTitle.classList.remove('line-through', 'text-gray-400', 'dark:text-gray-500');
                }
            }

            // 10. Move task card in Kanban board if exists
            const kanbanCard = document.getElementById('kanban-task-' + taskId);
            const targetStatusId = data.task?.status_id;
            const targetZone = document.getElementById('kanban-drop-zone-' + targetStatusId);
            if (kanbanCard && targetZone && kanbanCard.parentElement !== targetZone) {
                const fromZone = kanbanCard.parentElement;
                const placeholder = targetZone.querySelector('.empty-placeholder');
                if (placeholder) placeholder.remove();

                targetZone.appendChild(kanbanCard);
                kanbanCard.setAttribute('data-status-id', targetStatusId);
                kanbanCard.setAttribute('ondragstart', `handleKanbanDragStart(event, ${taskId}, ${targetStatusId})`);

                const kanbanTitle = kanbanCard.querySelector('h5');
                if (kanbanTitle) {
                    if (isCompleted) {
                        kanbanTitle.classList.add('line-through', 'text-gray-400', 'dark:text-gray-500');
                    } else {
                        kanbanTitle.classList.remove('line-through', 'text-gray-400', 'dark:text-gray-500');
                    }
                }

                if (fromZone && fromZone !== targetZone) {
                    const remaining = fromZone.querySelectorAll('[id^="kanban-task-"]');
                    if (remaining.length === 0 && !fromZone.querySelector('.empty-placeholder')) {
                        const emptyDiv = document.createElement('div');
                        emptyDiv.className = 'py-10 text-center text-gray-400 text-xs empty-placeholder';
                        emptyDiv.textContent = 'کاری در این ستون نیست';
                        fromZone.appendChild(emptyDiv);
                    }
                }

                if (typeof updateKanbanCounters === 'function') {
                    updateKanbanCounters();
                }
            }

            if (data.dashboard_stats && window.updateProjectDashboard) {
                window.updateProjectDashboard(data.dashboard_stats);
            } else if (window.refreshProjectDashboard) {
                window.refreshProjectDashboard(projectId || {{ $project->id }});
            }
        };

        window.updatePhasesProgress = function (phasesStats) {
            if (!phasesStats) return;
            const faDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            const toFa = n => (n === null || n === undefined) ? '' : String(n).replace(/[0-9]/g, d => faDigits[d]);

            Object.keys(phasesStats).forEach(phaseId => {
                const p = phasesStats[phaseId];
                const badge = document.getElementById('phase-progress-badge-' + phaseId);
                if (badge && p) {
                    const activeCount = p.active !== undefined ? p.active : p.total;
                    badge.textContent = `${toFa(p.completed)} از ${toFa(activeCount)} تکمیل شده (${toFa(p.progress)}٪)`;
                }
            });
        };

        function updateChecklistProgress(taskId, progress, doneCount, totalCount) {
            const bar = document.getElementById('checklist-bar-' + taskId);
            if (bar && progress !== undefined) {
                bar.style.width = progress + '%';
            }

            if (doneCount !== undefined && totalCount !== undefined) {
                const faDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                const doneFa = String(doneCount).replace(/[0-9]/g, d => faDigits[d]);
                const totalFa = String(totalCount).replace(/[0-9]/g, d => faDigits[d]);
                const text = `${doneFa} از ${totalFa}`;

                // 1. Checklist Section Counter (inside collapsed checklist area)
                const sectionCountEl = document.getElementById('checklist-count-' + taskId);
                if (sectionCountEl) {
                    sectionCountEl.textContent = text;
                }

                // 2. Task Card Header Badge Counter
                const badgeEl = document.getElementById('task-checklist-badge-' + taskId);
                const badgeTextEl = document.getElementById('task-checklist-badge-text-' + taskId);
                if (badgeTextEl) {
                    badgeTextEl.textContent = text;
                }
                if (badgeEl) {
                    if (totalCount > 0) {
                        badgeEl.classList.remove('hidden');
                    } else {
                        badgeEl.classList.add('hidden');
                    }
                }

                // 3. Kanban Card Badge Counter
                const kanbanBadgeEl = document.getElementById('kanban-checklist-badge-' + taskId);
                const kanbanBadgeTextEl = document.getElementById('kanban-checklist-badge-text-' + taskId);
                if (kanbanBadgeTextEl) {
                    kanbanBadgeTextEl.textContent = text;
                }
                if (kanbanBadgeEl) {
                    if (totalCount > 0) {
                        kanbanBadgeEl.classList.remove('hidden');
                        if (doneCount === totalCount) {
                            kanbanBadgeEl.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40';
                        } else {
                            kanbanBadgeEl.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl font-bold bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700';
                        }
                    } else {
                        kanbanBadgeEl.classList.add('hidden');
                    }
                }
            }
        }

        function quickAddChecklistItem(projectId, taskId) {
            const input = document.getElementById('quick-add-input-' + taskId);
            const title = input.value.trim();
            if (!title) return;

            fetch(`{{ url('user/projects/projects') }}/${projectId}/tasks/${taskId}/checklist`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({title})
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        input.value = '';
                        window.location.reload();
                    }
                })
                .catch(err => console.error('Checklist add error:', err));
        }

        function cancelChecklistItem(projectId, taskId, itemId, itemTitle) {
            if (!confirm(`آیا از لغو کار «${itemTitle}» اطمینان دارید؟ این عملیات غیرقابل بازگشت است.`)) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`{{ url('user/projects/projects') }}/${projectId}/tasks/${taskId}/checklist/${itemId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(data => {
                            throw new Error(data.message || 'خطا در لغو کار');
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        applyChecklistItemCanceledUI(taskId, itemId, data);
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {type: 'success', text: `کار «${data.item?.title || itemTitle}» لغو شد.`}
                        }));
                    }
                })
                .catch(err => {
                    console.error('Checklist cancel error:', err);
                    alert(err.message);
                });
        }

        function applyChecklistItemCanceledUI(taskId, itemId, data) {
            // Disable checkbox
            const cb = document.getElementById('checklist-checkbox-' + itemId);
            if (cb) {
                cb.checked = false;
                cb.disabled = true;
                cb.classList.add('cursor-not-allowed', 'opacity-50');
            }

            // Strikethrough & color on title
            const titleSpan = document.getElementById('checklist-item-title-' + itemId);
            if (titleSpan) {
                titleSpan.classList.add('line-through', 'text-rose-500', 'dark:text-rose-400', 'opacity-75');
            }

            // Remove edit & cancel buttons
            const editBtn = document.getElementById('checklist-edit-btn-' + itemId);
            if (editBtn) editBtn.remove();
            const cancelBtn = document.getElementById('checklist-cancel-btn-' + itemId);
            if (cancelBtn) cancelBtn.remove();

            // Status badge in row
            let statusBadge = document.getElementById('checklist-status-badge-' + itemId);
            if (!statusBadge && titleSpan) {
                statusBadge = document.createElement('span');
                statusBadge.id = 'checklist-status-badge-' + itemId;
                titleSpan.parentNode.appendChild(statusBadge);
            }
            if (statusBadge) {
                statusBadge.textContent = 'لغو شده';
                statusBadge.style.backgroundColor = '#ef444418';
                statusBadge.style.color = '#ef4444';
                statusBadge.style.borderColor = '#ef444433';
                statusBadge.className = 'inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded-md border shrink-0 bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border-rose-200/60 dark:border-rose-800/40';
            }

            // Details status text & icon
            const detailsText = document.getElementById('checklist-details-status-text-' + itemId);
            const detailsIcon = document.getElementById('checklist-details-status-icon-' + itemId);
            if (detailsText) {
                detailsText.textContent = 'لغو شده';
                detailsText.style.color = '#ef4444';
            }
            if (detailsIcon) {
                detailsIcon.style.backgroundColor = '#ef444418';
                detailsIcon.style.color = '#ef4444';
            }

            if (data.task_progress !== undefined) {
                updateChecklistProgress(taskId, data.task_progress, data.checklist_done, data.checklist_total);
            }

            // Replace comment form in inline comments with disabled notice
            const commentForm = document.getElementById('item-comment-form-' + itemId);
            if (commentForm) {
                commentForm.outerHTML = '<div class="p-2 text-center text-[11px] text-gray-400 bg-white/60 dark:bg-gray-800/60 rounded-xl border border-dashed border-gray-200 dark:border-gray-700/60 font-medium" id="item-comment-form-' + itemId + '">امکان ثبت کامنت برای کارهای لغو شده وجود ندارد.</div>';
            }

            // Remove comment delete buttons for this canceled item
            const commentsList = document.getElementById('checklist-comments-list-' + itemId);
            if (commentsList) {
                commentsList.querySelectorAll('button[onclick^="deleteItemComment"]').forEach(btn => btn.remove());
            }

            // Update Task Status Badge and Icon
            if (data.task?.status) {
                window.updateTaskStatusBadgeAndIcon(taskId, data.task.status);
            }

            // Live update dashboard
            if (data.dashboard_stats && window.updateProjectDashboard) {
                window.updateProjectDashboard(data.dashboard_stats);
            }
        }

        window.cancelChecklistItemSuccessCallback = applyChecklistItemCanceledUI;

        function deleteChecklistItem(projectId, taskId, itemId) {
            if (!confirm('آیا از حذف این کار اطمینان دارید؟')) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`{{ url('user/projects/projects') }}/${projectId}/tasks/${taskId}/checklist/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const row = document.getElementById('checklist-row-' + itemId);
                        if (row) row.remove();

                        if (data.task_progress !== undefined) {
                            updateChecklistProgress(taskId, data.task_progress, data.checklist_done, data.checklist_total);
                        }

                        // Update Task Status Badge and Icon
                        if (data.task?.status) {
                            window.updateTaskStatusBadgeAndIcon(taskId, data.task.status);
                        }

                        // Live update dashboard
                        if (data.dashboard_stats && window.updateProjectDashboard) {
                            window.updateProjectDashboard(data.dashboard_stats);
                        }
                    } else {
                        alert(data.message || 'خطا در حذف کار');
                    }
                })
                .catch(err => {
                    console.error('Checklist delete error:', err);
                    alert('خطا در حذف کار');
                });
        }

        function openEditChecklistItem(projectId, taskId, itemId, currentTitle, currentDueDate = '') {
            const newTitle = prompt('ویرایش عنوان آیتم:', currentTitle);
            if (newTitle === null) return;
            if (!newTitle.trim()) {
                alert('عنوان نمی‌تواند خالی باشد.');
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`{{ url('user/projects/projects') }}/${projectId}/tasks/${taskId}/checklist/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: JSON.stringify({title: newTitle.trim(), due_date: currentDueDate || null})
            })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(data => {
                            throw new Error(data.message || 'خطا در ویرایش کار');
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        const span = document.getElementById('checklist-item-title-' + itemId);
                        if (span) span.textContent = data.item.title;
                    }
                })
                .catch(err => {
                    console.error('Checklist edit error:', err);
                    alert('⚠️ ' + err.message);
                });
        }

        function submitItemComment(projectId, taskId, itemId) {
            const input = document.getElementById('item-comment-input-' + itemId);
            if (!input) return;

            const body = input.value.trim();
            if (!body) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`{{ url('user/projects/projects') }}/${projectId}/tasks/${taskId}/checklist/${itemId}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({body: body})
            })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) {
                        throw new Error(data.message || 'خطا در ثبت کامنت');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        input.value = '';

                        const list = document.getElementById('checklist-comments-list-' + itemId);
                        if (list) {
                            const emptyHint = list.querySelector('.empty-item-comments-hint');
                            if (emptyHint) emptyHint.remove();

                            const cId = data.comment.id;
                            const userName = data.comment.user?.name || 'کاربر';
                            const initial = userName.charAt(0);

                            const d = document.createElement('div');
                            d.className = 'p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/70 space-y-1 text-xs shadow-2xs';
                            d.id = 'checklist-comment-' + cId;
                            d.innerHTML = `
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 text-[9px] flex items-center justify-center font-bold">
                                    ${initial}
                                </span>
                                <strong class="text-[11px] text-gray-800 dark:text-gray-200">${userName}</strong>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] text-gray-400">هم‌اکنون</span>
                                <button type="button" onclick="deleteItemComment(${projectId}, ${taskId}, ${itemId}, ${cId})" class="text-gray-400 hover:text-red-500 p-0.5" title="حذف کامنت">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-700 dark:text-gray-300 leading-relaxed pr-5.5 whitespace-pre-line">${data.comment.body}</p>
                    `;
                            list.prepend(d);
                        }

                        const countEl = document.getElementById('checklist-comments-count-' + itemId);
                        if (countEl) countEl.textContent = data.comments_count;
                    }
                })
                .catch(err => {
                    console.error('Item comment submit error:', err);
                    alert(err.message);
                });
        }

        function deleteItemComment(projectId, taskId, itemId, commentId) {
            if (!confirm('آیا از حذف این کامنت اطمینان دارید؟')) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`{{ url('user/projects/projects') }}/${projectId}/tasks/${taskId}/checklist/${itemId}/comments/${commentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const el = document.getElementById('checklist-comment-' + commentId);
                        if (el) el.remove();

                        const countEl = document.getElementById('checklist-comments-count-' + itemId);
                        if (countEl) countEl.textContent = data.comments_count;

                        const list = document.getElementById('checklist-comments-list-' + itemId);
                        if (list && list.children.length === 0) {
                            list.innerHTML = '<div class="py-2.5 text-center text-gray-400 text-[11px] empty-item-comments-hint">هنوز کامنتی برای این کار ثبت نشده است.</div>';
                        }
                    } else {
                        alert(data.message || 'خطا در حذف کامنت');
                    }
                })
                .catch(err => console.error('Item comment delete error:', err));
        }

        // --- Real-time SSE Connection for Tasks and Checklists ---
        let tasksSse = null;
        let lastTasksEventId = Math.floor(Date.now());

        function connectTasksSse() {
            if (tasksSse) {
                tasksSse.close();
                tasksSse = null;
            }

            const url = `{{ url('user/projects/projects') }}/{{ $project->id }}/tasks/sse?last_event_id=${lastTasksEventId}`;
            tasksSse = new EventSource(url);

            tasksSse.addEventListener('checklist_item_toggled', (e) => {
                try {
                    const data = JSON.parse(e.data);
                    if (data.event_id) lastTasksEventId = data.event_id;
                    if (window.applyChecklistToggleUpdate) {
                        window.applyChecklistToggleUpdate(data, false);
                    }
                } catch (err) {
                    console.error('SSE checklist_item_toggled error:', err);
                }
            });

            tasksSse.addEventListener('checklist_updated', (e) => {
                try {
                    const data = JSON.parse(e.data);
                    if (data.event_id) lastTasksEventId = data.event_id;
                    if (!data.is_my_action) {
                        window.location.reload();
                    }
                } catch (err) {
                    console.error('SSE checklist_updated error:', err);
                }
            });

            tasksSse.addEventListener('task_updated', (e) => {
                try {
                    const data = JSON.parse(e.data);
                    if (data.event_id) lastTasksEventId = data.event_id;
                    if (!data.is_my_action) {
                        if (data.action === 'status_updated' && data.task_id && data.task_status) {
                            if (window.updateTaskStatusBadgeAndIcon) {
                                window.updateTaskStatusBadgeAndIcon(data.task_id, data.task_status);
                            }
                            if (data.task_progress !== undefined && typeof updateChecklistProgress === 'function') {
                                updateChecklistProgress(data.task_id, data.task_progress, data.checklist_done, data.checklist_total);
                            }
                        } else {
                            window.location.reload();
                        }
                    }
                } catch (err) {
                    console.error('SSE task_updated error:', err);
                }
            });

            tasksSse.addEventListener('reconnect', (e) => {
                try {
                    const data = JSON.parse(e.data);
                    if (data.last_event_id) lastTasksEventId = data.last_event_id;
                } catch (err) {
                }
                if (tasksSse) tasksSse.close();
                setTimeout(connectTasksSse, 500);
            });

            tasksSse.onerror = () => {
                if (tasksSse) tasksSse.close();
                setTimeout(connectTasksSse, 3000);
            };
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', connectTasksSse);
        } else {
            connectTasksSse();
        }
    </script>
@endpush
