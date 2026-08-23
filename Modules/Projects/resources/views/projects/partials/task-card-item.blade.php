@php
    use Illuminate\Support\Carbon;
    $taskSlug = 'task_' . $task->id;
    $maxSubtaskDueDate = $task->due_date
        ? (function_exists('jdate') ? jdate($task->due_date)->format('Y/m/d') : $task->due_date->format('Y/m/d'))
        : ($project->end_date ? (function_exists('jdate') ? jdate($project->end_date)->format('Y/m/d') : $project->end_date->format('Y/m/d')) : '');
    $maxSubtaskDueDateLabel = $task->due_date ? 'سررسید گروه' : ($project->end_date ? 'پایان پروژه' : '');
@endphp

<div
    class="bg-gray-50/60 dark:bg-gray-900/30 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 p-5 transition-all hover:border-indigo-200 dark:hover:border-indigo-800/60 space-y-4 shadow-xs"
    :class="(selectedTasks.includes({{ $task->id }}) || selectedTasks.includes('{{ $task->id }}')) ? 'ring-2 ring-indigo-500/50 border-indigo-400 dark:border-indigo-600 bg-indigo-50/20 dark:bg-indigo-900/10' : ''"
    id="task-card-{{ $task->id }}">

    {{-- Task Top Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 cursor-pointer select-none"
         @click="toggleTask('{{ $taskSlug }}')">
        <div class="flex items-start sm:items-center gap-3 min-w-0">
            @if(!$project->isCanceled() && ($project->userHasPermission(auth()->id(), 'tasks.delete') || $project->isManager(auth()->id()) || $isSuperAdmin))
                <div class="flex items-center shrink-0" @click.stop>
                    <input type="checkbox"
                           value="{{ $task->id }}"
                           x-model="selectedTasks"
                           class="w-4 h-4 rounded-md text-indigo-600 focus:ring-2 focus:ring-indigo-500/20 border-gray-300 dark:border-gray-600 dark:bg-gray-700/80 cursor-pointer shadow-2xs transition-all"
                           title="انتخاب گروه برای عملیات گروهی">
                </div>
            @endif

            {{-- Collapse / Expand Chevron Icon --}}
            <button type="button"
                    class="p-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 transition-transform duration-200 shadow-xs shrink-0"
                    :class="collapsedTasks['{{ $taskSlug }}'] ? 'rotate-180' : ''">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <span
                class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-xs transition-colors duration-200"
                id="task-icon-box-{{ $task->id }}"
                style="background: {{ $task->status?->color ?? '#6366f1' }}18; border: 1.5px solid {{ $task->status?->color ?? '#6366f1' }}33">
                <span class="w-3 h-3 rounded-full transition-colors duration-200"
                      id="task-icon-dot-{{ $task->id }}"
                      style="background: {{ $task->status?->color ?? '#6366f1' }}"></span>
            </span>
                <div class="min-w-0">
                    <h5 id="task-card-title-{{ $task->id }}"
                        class="font-bold text-gray-900 dark:text-white text-sm sm:text-base leading-snug truncate {{ $task->status?->isCompleted() ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">
                        {{ $task->title }}
                    </h5>
                    @if($task->description)
                        <p class="text-xs text-gray-400 mt-0.5 leading-relaxed line-clamp-2">{{ $task->description }}</p>
                    @endif
                </div>
        </div>

        {{-- Badges & Actions --}}
        <div class="flex items-center gap-2 flex-wrap shrink-0" @click.stop>
            {{-- Checklist Items Count Badge --}}
            <span id="task-checklist-badge-{{ $task->id }}"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/40 shadow-xs {{ $task->activeChecklistCount() > 0 ? '' : 'hidden' }}"
                  title="کارهای تکمیل شده">
                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <span
                    id="task-checklist-badge-text-{{ $task->id }}">{{ $faNum($task->completedChecklistCount()) }} از {{ $faNum($task->activeChecklistCount()) }}</span>
            </span>

            {{-- Due Date Pill --}}
            @if($task->due_date)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 shadow-xs" title="تاریخ سررسید گروه">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">سررسید:</span>
                    <span>{{ function_exists('jdate') ? $faNum(jdate($task->due_date)->format('Y/m/d')) : $task->due_date->format('Y/m/d') }}</span>
                </span>
            @endif
            @if($task->assignee || $task->manager)
                @php $managerUser = $task->manager ?? $task->assignee; @endphp
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/40"
                    title="مدیر گروه">
                    <span
                        class="w-4 h-4 rounded-full bg-indigo-200 dark:bg-indigo-700 text-[9px] flex items-center justify-center font-black">
                        {{ mb_substr($managerUser->name, 0, 1) }}
                    </span>
                    <span>مدیر گروه: {{ $managerUser->name }}</span>
                </span>
            @endif
            <span id="task-status-badge-{{ $task->id }}"
                  class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold shadow-2xs border transition-all"
                  style="background-color: {{ $task->status?->color ?? '#6366f1' }}18; color: {{ $task->status?->color ?? '#6366f1' }}; border-color: {{ $task->status?->color ?? '#6366f1' }}33">
                <span class="w-2 h-2 rounded-full shrink-0"
                      style="background-color: {{ $task->status?->color ?? '#6366f1' }}"></span>
                <span id="task-status-name-{{ $task->id }}">{{ $task->status?->name ?? 'در صف' }}</span>
            </span>

            {{-- Actions --}}
            @if($project->userHasPermission(auth()->id(), 'tasks.edit') || $project->userHasPermission(auth()->id(), 'tasks.delete') || $project->isManager(auth()->id()) || $project->userHasPermission(auth()->id(), 'tasks.cancel'))
                <div class="flex items-center gap-1">
                    {{-- Cancel Task Group Button --}}
                    @if(!$task->isCanceled())
                        @can('cancelTasks', $project)
                            <button type="button" id="task-cancel-btn-{{ $task->id }}"
                                    onclick="cancelTaskGroup({{ $project->id }}, {{ $task->id }}, '{{ addslashes($task->title) }}')"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                    title="لغو گروه (غیرقابل بازگشت)">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                            </button>
                        @endcan
                    @endif

                    @can('editTasks', $project)
                        @if(!$task->isCanceled())
                            <button type="button" id="task-edit-btn-{{ $task->id }}"
                                    @click='openEditModal(@json($task), @json($task->checklistItems))'
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
                                    title="ویرایش گروه">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        @endif
                    @endcan
                    @can('deleteTasks', $project)
                        @if(!$task->isCanceled())
                            <form method="POST" id="task-delete-form-{{ $task->id }}"
                                  action="{{ route('projects.projects.tasks.destroy', ['project' => $project->id, 'task' => $task->id]) }}"
                                  onsubmit="return confirm('آیا از حذف این گروه اطمینان دارید؟')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" id="task-delete-btn-{{ $task->id }}"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                        title="حذف گروه">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            @endif
        </div>
    </div>
    <div x-show="!collapsedTasks['{{ $taskSlug }}']"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="pt-3 border-t border-gray-200/70 dark:border-gray-700/50 space-y-2"
         id="checklist-section-{{ $task->id }}">

        <div class="flex items-center justify-between text-xs">
            <span class="font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                کارها:
            </span>
            <div class="flex items-center gap-2">
                <div class="w-24 bg-gray-200 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-emerald-500 h-full rounded-full transition-all duration-300"
                         id="checklist-bar-{{ $task->id }}"
                         style="width: {{ $task->checklistProgress() }}%"></div>
                </div>
                <span class="text-[11px] text-gray-400 font-bold" id="checklist-count-{{ $task->id }}">
                    {{ $faNum($task->completedChecklistCount()) }} از {{ $faNum($task->activeChecklistCount()) }}
                </span>
            </div>
        </div>

        <div class="space-y-2" id="checklist-items-{{ $task->id }}">
            @foreach($task->checklistItems as $item)
                @php
                    $itemOverdue = !$item->is_done && $item->due_date
                        && Carbon::parse($item->due_date)->endOfDay()->isPast();
                @endphp

                @php
                    $itemAssignees = $item->assignees;
                    if ($itemAssignees->isEmpty() && $item->assignee) {
                        $itemAssignees = collect([$item->assignee]);
                    }
                    $isCompletedForMe = $item->isCompletedBy(auth()->id());
                @endphp

                <div x-data="{ showItemDetails: false, showItemComments: false }"
                     data-checklist-assigned-to="{{ $itemAssignees->pluck('id')->join(',') ?: ($item->assigned_to ?? 0) }}"
                     class="space-y-1.5"
                     id="checklist-item-wrapper-{{ $item->id }}">
                    <div
                        class="group flex items-center justify-between gap-2 p-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/70 dark:border-gray-700/60 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all shadow-xs"
                        id="checklist-item-{{ $item->id }}">
                        <div class="flex items-center gap-1.5 min-w-0 flex-1">
                            <button type="button"
                                    @click="showItemDetails = !showItemDetails"
                                    class="p-1 rounded-lg transition-all duration-200 shrink-0 cursor-pointer"
                                    :class="showItemDetails
                                        ? 'bg-indigo-600 text-white shadow-xs'
                                        : 'bg-gray-50 dark:bg-gray-700/60 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/40'"
                                    title="نمایش جزئیات کامل کار">
                                <svg class="w-3.5 h-3.5 transition-transform duration-200"
                                     :class="showItemDetails ? 'rotate-180' : ''"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <label class="flex items-center gap-2 cursor-pointer text-xs select-none min-w-0 flex-1">
                                <input type="checkbox"
                                       id="checklist-checkbox-{{ $item->id }}"
                                       onchange="toggleChecklistItem({{ $project->id }}, {{ $task->id }}, {{ $item->id }}, this)"
                                       @checked($isCompletedForMe)
                                       @disabled($task->isCanceled() || $item->isCanceled())
                                       class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300 {{ ($task->isCanceled() || $item->isCanceled()) ? 'cursor-not-allowed opacity-50' : '' }}">
                                <span
                                    class="font-medium truncate {{ $item->is_done ? 'line-through text-gray-400 dark:text-gray-500' : ($item->isCanceled() ? 'line-through text-rose-500 dark:text-rose-400 opacity-75' : 'text-gray-800 dark:text-gray-200') }}"
                                    id="checklist-item-title-{{ $item->id }}">
                                    {{ $item->title }}
                                </span>
                            </label>
                            @php
                                $itemStatus = $item->status;
                                if (!$itemStatus && $item->status_id && isset($checklistStatuses)) {
                                    $itemStatus = $checklistStatuses->firstWhere('id', $item->status_id);
                                }
                                if (!$itemStatus && isset($checklistStatuses)) {
                                    if ($item->is_done) {
                                        $itemStatus = $checklistStatuses->first(fn($s) => $s->isCompleted());
                                    } elseif ($itemOverdue) {
                                        $itemStatus = $checklistStatuses->first(fn($s) => $s->isDelayed());
                                    }
                                }
                                $itemStatusName = $itemStatus?->name ?? ($item->is_done ? 'تکمیل شده' : '');
                                $itemStatusColor = $itemStatus?->color ?? ($item->is_done ? '#10b981' : '#6b7280');
                            @endphp

                            @if($itemStatusName)
                                <span id="checklist-status-badge-{{ $item->id }}"
                                      class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded-md border shrink-0 shadow-2xs transition-all"
                                      style="background-color: {{ $itemStatusColor }}18; color: {{ $itemStatusColor }}; border-color: {{ $itemStatusColor }}33">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                          style="background-color: {{ $itemStatusColor }}"></span>
                                    <span id="checklist-status-name-{{ $item->id }}">{{ $itemStatusName }}</span>
                                </span>
                            @else
                                <span id="checklist-status-badge-{{ $item->id }}"
                                      class="hidden text-[10px] font-bold px-1.5 py-0.5 rounded-md border shrink-0"></span>
                            @endif
                            @if($itemAssignees->isNotEmpty())
                                <div id="checklist-assignees-badge-{{ $item->id }}"
                                     class="hidden sm:inline-flex items-center gap-1 shrink-0">
                                    @if($itemAssignees->count() === 1)
                                        <span
                                            class="inline-flex items-center gap-1 text-[10px] text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded-md font-bold border border-indigo-100 dark:border-indigo-800/40">
                                            {{ $itemAssignees->first()->name }}
                                        </span>
                                    @else
                                        @php
                                            $doneCount = $item->assigneesCompletedCount();
                                            $totalCount = $itemAssignees->count();
                                        @endphp
                                        <span
                                            class="inline-flex items-center gap-1 text-[10px] {{ $item->is_done ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/30 border-emerald-200/60 dark:border-emerald-800/40' : 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 border-indigo-100 dark:border-indigo-800/40' }} px-1.5 py-0.5 rounded-md font-bold border"
                                            title="{{ $itemAssignees->pluck('name')->join('، ') }}">
                                            <span>{{ $totalCount }} مسئول</span>
                                            <span class="text-[9px] font-bold opacity-80">({{ $faNum($doneCount) }}/{{ $faNum($totalCount) }})</span>
                                        </span>
                                    @endif
                                </div>
                            @endif
                            @if($item->due_date)
                                <span id="checklist-duedate-badge-{{ $item->id }}"
                                      class="hidden md:inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded-md border shrink-0 bg-gray-50 dark:bg-gray-700/40 text-gray-500 dark:text-gray-300 border-gray-200/60 dark:border-gray-600/40"
                                      title="تاریخ سررسید کار">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ function_exists('jdate') ? $faNum(jdate($item->due_date)->format('Y/m/d')) : $item->due_date->format('Y/m/d') }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button"
                                    @click="showItemComments = !showItemComments"
                                    class="inline-flex items-center gap-1 px-1.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer"
                                    :class="showItemComments ? 'bg-indigo-600 text-white shadow-xs' : ({{ $item->comments->count() }} > 0 ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/60' : 'text-gray-400 hover:text-indigo-600 hover:bg-gray-100 dark:hover:bg-gray-700')"
                                    title="مشاهده و ثبت کامنت‌های این کار">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                                <span id="checklist-comments-count-{{ $item->id }}" class="text-[10px] font-bold">
                                    {{ $faNum($item->comments->count()) }}
                                </span>
                            </button>

                            @if(!$task->isCanceled() && !$item->isCanceled())
                                @can('manageTasks', $project)
                                    <button type="button"
                                            id="checklist-edit-btn-{{ $item->id }}"
                                            onclick="openSubtaskEdit({{ $project->id }}, {{ $task->id }}, {{ $item->id }}, {{ json_encode($task->title, JSON_HEX_APOS | JSON_HEX_QUOT) }}, null, false, '{{ $maxSubtaskDueDate }}', '{{ $maxSubtaskDueDateLabel }}')"
                                            class="checklist-action-btn-{{ $task->id }} opacity-0 group-hover:opacity-100 p-1 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-all"
                                            title="ویرایش کار">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                @endcan
                                @can('cancelTasks', $project)
                                    <button type="button"
                                            id="checklist-cancel-btn-{{ $item->id }}"
                                            onclick="cancelChecklistItem({{ $project->id }}, {{ $task->id }}, {{ $item->id }}, '{{ addslashes($item->title) }}')"
                                            class="checklist-action-btn-{{ $task->id }} opacity-0 group-hover:opacity-100 p-1 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all"
                                            title="لغو کار (غیرقابل بازگشت)">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                    </button>
                                @endcan
                            @endif
                        </div>
                    </div>
                    <div x-show="showItemDetails" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="mr-5 p-3 sm:p-3.5 rounded-2xl bg-gradient-to-br from-gray-50 to-indigo-50/40 dark:from-gray-900/70 dark:to-indigo-950/20 border border-gray-200/70 dark:border-gray-700/60 space-y-3 shadow-2xs">

                        <div class="flex items-center justify-between gap-2">
                            <span
                                class="text-[11px] font-bold text-gray-600 dark:text-gray-300 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                جزئیات کامل کار:
                                <span
                                    class="text-indigo-600 dark:text-indigo-400 truncate max-w-[150px]">{{ $item->title }}</span>
                            </span>
                            <button type="button" @click="showItemDetails = false"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs p-0.5"
                                    title="بستن">✕
                            </button>
                        </div>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
                            <div
                                class="flex items-center gap-2 p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 min-w-0">
                                <span id="checklist-details-status-icon-{{ $item->id }}"
                                      class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                      style="background-color: {{ $itemStatusColor }}18; color: {{ $itemStatusColor }};">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0"
                                          style="background-color: {{ $itemStatusColor }}"></span>
                                </span>
                                <div class="min-w-0">
                                    <div class="text-[10px] font-bold text-gray-400">وضعیت کار</div>
                                    <div id="checklist-details-status-text-{{ $item->id }}"
                                         class="text-[11px] font-bold truncate"
                                         style="color: {{ $itemStatusColor }};">
                                        {{ $itemStatusName ?: '—' }}
                                    </div>
                                </div>
                            </div>
                            <div
                                class="flex items-center gap-2 p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 min-w-0 {{ $itemAssignees->count() > 1 ? 'col-span-2' : '' }}">
                                <span
                                    class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[10px] font-bold text-gray-400">
                                        {{ $itemAssignees->count() > 1 ? 'مسئولین اجرا (' . $faNum($itemAssignees->count()) . ' نفر)' : 'مسئول اجرا' }}
                                    </div>
                                    <div class="text-[11px] font-bold text-gray-800 dark:text-gray-200"
                                         id="checklist-details-assignees-{{ $item->id }}">
                                        @if($itemAssignees->isEmpty())
                                            تعیین نشده
                                        @elseif($itemAssignees->count() === 1)
                                            {{ $itemAssignees->first()->name }}
                                        @else
                                            <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                                @foreach($itemAssignees as $assigneeUser)
                                                    @php $userIsDone = (bool)($assigneeUser->pivot?->is_done ?? false); @endphp
                                                    <span
                                                        class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded {{ $userIsDone ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                                        <span>{{ $userIsDone ? '✓' : '○' }}</span>
                                                        <span>{{ $assigneeUser->name }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if($item->due_date)
                                <div
                                    class="flex items-center gap-2 p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 min-w-0">
                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 bg-sky-50 dark:bg-sky-900/40 text-sky-600 dark:text-sky-300">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="text-[10px] font-bold text-gray-400">تاریخ سررسید</div>
                                        <div class="text-[11px] font-bold truncate text-gray-800 dark:text-gray-200">
                                            {{ function_exists('jdate') ? $faNum(jdate($item->due_date)->format('Y/m/d')) : $item->due_date->format('Y/m/d') }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div
                                class="flex items-center gap-2 p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 min-w-0">
                                <span
                                    class="w-7 h-7 rounded-lg bg-gray-100 dark:bg-gray-700/60 text-gray-500 dark:text-gray-300 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <div class="text-[10px] font-bold text-gray-400">تاریخ ثبت</div>
                                    <div class="text-[11px] font-bold text-gray-800 dark:text-gray-200 truncate">
                                        {{ function_exists('jdate') ? $faNum(jdate($item->created_at)->format('Y/m/d')) : $item->created_at->format('Y/m/d') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($item->description)
                            <div
                                class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60">
                                <div class="text-[10px] font-bold text-gray-400 mb-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 6h16M4 12h16M4 18h7"/>
                                    </svg>
                                    توضیحات
                                </div>
                                <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">{{ $item->description }}</p>
                            </div>
                        @endif
                    </div>
                    <div x-show="showItemComments" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="p-3 rounded-2xl bg-indigo-50/50 dark:bg-gray-900/70 border border-indigo-100/80 dark:border-gray-700/60 space-y-2.5 shadow-2xs">

                        <div class="flex items-center justify-between">
                            <span
                                class="text-[11px] font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                                کامنت‌های کار: <span
                                    class="text-indigo-600 dark:text-indigo-400 font-bold truncate max-w-[160px]">{{ $item->title }}</span>
                            </span>
                            <button type="button" @click="showItemComments = false"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs p-0.5"
                                    title="بستن">✕
                            </button>
                        </div>

                        <div class="space-y-1.5 max-h-48 overflow-y-auto pr-0.5"
                             id="checklist-comments-list-{{ $item->id }}">
                            @forelse($item->comments as $c)
                                <div
                                    class="p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/70 space-y-1 text-xs shadow-2xs"
                                    id="checklist-comment-{{ $c->id }}">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5">
                                            <span
                                                class="w-4 h-4 rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 text-[9px] flex items-center justify-center font-bold">
                                                {{ mb_substr($c->user?->name ?? 'U', 0, 1) }}
                                            </span>
                                            <strong
                                                class="text-[11px] text-gray-800 dark:text-gray-200">{{ $c->user?->name }}</strong>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[10px] text-gray-400">
                                                {{ function_exists('jdate') ? $faNum(jdate($c->created_at)->format('Y/m/d H:i')) : $c->created_at->format('Y/m/d H:i') }}
                                            </span>
                                            @if(!$project->isCanceled() && !$task->isCanceled() && !$item->isCanceled() && ($c->user_id === auth()->id() || $project->isManager(auth()->id()) || $project->userHasPermission(auth()->id(), 'comments.delete')))
                                                <button type="button"
                                                        onclick="deleteItemComment({{ $project->id }}, {{ $task->id }}, {{ $item->id }}, {{ $c->id }})"
                                                        class="text-gray-400 hover:text-red-500 p-0.5"
                                                        title="حذف کامنت">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-gray-700 dark:text-gray-300 leading-relaxed pr-5.5 whitespace-pre-line">{{ $c->body }}</p>
                                </div>
                            @empty
                                <div class="py-2.5 text-center text-gray-400 text-[11px] empty-item-comments-hint">
                                    هنوز کامنتی برای این کار ثبت نشده است.
                                </div>
                            @endforelse
                        </div>

                        @if(!$project->isCanceled() && !$task->isCanceled() && !$item->isCanceled())
                            <div class="flex items-center gap-1.5 pt-0.5" id="item-comment-form-{{ $item->id }}">
                                <input type="text" id="item-comment-input-{{ $item->id }}"
                                       placeholder="ثبت کامنت جدید برای این کار..."
                                       @keydown.enter.prevent="submitItemComment({{ $project->id }}, {{ $task->id }}, {{ $item->id }})"
                                       class="flex-1 rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white shadow-2xs">
                                <button type="button"
                                        onclick="submitItemComment({{ $project->id }}, {{ $task->id }}, {{ $item->id }})"
                                        class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-2xs transition-all flex items-center gap-1 shrink-0">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    ارسال
                                </button>
                            </div>
                        @else
                            <div
                                class="p-2 text-center text-[11px] text-gray-400 bg-white/60 dark:bg-gray-800/60 rounded-xl border border-dashed border-gray-200 dark:border-gray-700/60 font-medium"
                                id="item-comment-form-{{ $item->id }}">
                                امکان ثبت کامنت برای کارهای لغو شده وجود ندارد.
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Add Row with Modal Trigger --}}
        @if(!$task->isCanceled())
            @can('createTasks', $project)
                <div class="flex items-center gap-2 pt-1.5" id="quick-add-container-{{ $task->id }}">
                    <input type="text" id="quick-add-input-{{ $task->id }}"
                           placeholder="+ افزودن کار..."
                           onclick="openSubtaskCreate({{ $project->id }}, {{ $task->id }}, {{ json_encode($task->title, JSON_HEX_APOS | JSON_HEX_QUOT) }}, false, '{{ $maxSubtaskDueDate }}', '{{ $maxSubtaskDueDateLabel }}')"
                           class="w-full rounded-xl border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-1.5 text-xs focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:text-white transition-all cursor-pointer"
                           readonly>
                    <button type="button"
                            onclick="openSubtaskCreate({{ $project->id }}, {{ $task->id }}, {{ json_encode($task->title, JSON_HEX_APOS | JSON_HEX_QUOT) }}, false, '{{ $maxSubtaskDueDate }}', '{{ $maxSubtaskDueDateLabel }}')"
                            class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 text-xs font-bold hover:bg-emerald-100 transition-all shrink-0">
                        افزودن
                    </button>
                </div>
            @endcan
        @endif
    </div>
</div>
