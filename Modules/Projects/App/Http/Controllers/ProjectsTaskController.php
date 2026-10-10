<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Projects\App\Exceptions\InvalidStatusTransitionException;
use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectActivity;
use Modules\Projects\App\Http\Models\ProjectPhase;
use Modules\Projects\App\Http\Models\ProjectSetting;
use Modules\Projects\App\Http\Models\ProjectStatus;
use Modules\Projects\App\Http\Models\ProjectTask;
use App\Models\User;
use Modules\Projects\App\Http\Requests\StoreProjectTaskRequest;
use Modules\Projects\App\Services\ProjectsService;
use Modules\Projects\App\Services\ProjectsUserVisibilityService;
use Modules\Projects\App\Services\StatusTransitionService;
use Modules\Projects\App\Traits\HandlesJalaliDate;

class ProjectsTaskController extends Controller
{
    use HandlesJalaliDate;

    public function __construct(
        private StatusTransitionService $transitionSvc,
        private ProjectsService         $projectsSvc
    )
    {
    }

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()
            ->with(['status', 'assignee', 'checklistItems.status'])
            ->orderBy('sort_order')
            ->get();

        return response()->json($tasks);
    }

    public function store(StoreProjectTaskRequest $request, Project $project)
    {
        $this->authorize('createTasks', $project);

        $task = DB::transaction(function () use ($request, $project) {
            $data = $request->safe()->except('checklist');
            $data['project_id'] = $project->id;
            $data['created_by'] = auth()->id();
            $managerId = !empty($data['assigned_to']) ? (int)$data['assigned_to'] : (!empty($data['manager_id']) ? (int)$data['manager_id'] : null);
            if ($managerId && !ProjectsUserVisibilityService::isSuperAdmin(auth()->user())) {
                $targetManager = User::find($managerId);
                if ($targetManager && !ProjectsUserVisibilityService::canViewUser($targetManager, auth()->user())) {
                    throw ValidationException::withMessages([
                        'assigned_to' => 'شما مجاز به انتخاب این کاربر به عنوان مدیر گروه نیستید.'
                    ]);
                }
            }
            $data['manager_id'] = $managerId;
            $data['assigned_to'] = $managerId;

            if ($managerId && !$project->members()->where('user_id', $managerId)->exists()) {
                $project->members()->create(['user_id' => $managerId, 'role' => 'editor']);
            }

            $defaultTaskStatus = ProjectSetting::get('projects_default_task_status_id');
            $data['status_id'] = $data['status_id'] ?? ($defaultTaskStatus ?: ProjectStatus::defaultFor('task')?->id);
            $data['sort_order'] = (int)$project->tasks()->max('sort_order') + 1;
            $data['due_date'] = $this->convertJalaliDate($request->input('due_date'));

            if ($project->end_date && !empty($data['due_date'])) {
                if (strtotime($data['due_date']) > strtotime($project->end_date->format('Y-m-d'))) {
                    $projectEndDateJalali = function_exists('jdate') ? jdate($project->end_date)->format('Y/m/d') : $project->end_date->format('Y/m/d');
                    throw ValidationException::withMessages([
                        'due_date' => "تاریخ سررسید گروه نمی‌تواند پس از تاریخ پایان پروژه ({$projectEndDateJalali}) باشد."
                    ]);
                }
            }

            $status = isset($data['status_id']) ? ProjectStatus::find($data['status_id']) : null;
            if ($status?->isCompleted()) {
                $data['completed_at'] = now();
            }

            if (!empty($data['phase_id'])) {
                $phase = ProjectPhase::find($data['phase_id']);
                if ($phase) {
                    $data['group_name'] = $phase->name;
                }
            } elseif (!empty($data['group_name'])) {
                $phase = $project->phases()->firstOrCreate(['name' => trim($data['group_name'])], [
                    'sort_order' => (int)$project->phases()->max('sort_order') + 1,
                ]);
                $data['phase_id'] = $phase->id;
                $data['group_name'] = $phase->name;
            } else {
                $data['phase_id'] = null;
                $data['group_name'] = null;
            }

            $task = ProjectTask::create($data);

            $progressMode = ProjectSetting::get('projects_progress_mode', 'auto_tasks');
            if ($progressMode === 'auto_tasks') {
                $project->refreshProgress();
            }

            $checklist = $request->input('checklist', []);
            if (is_string($checklist)) {
                $checklist = json_decode($checklist, true) ?? [];
            }
            if (!is_array($checklist)) {
                $checklist = [];
            }

            foreach ($checklist as $i => $item) {
                if (empty($item['title'])) {
                    continue;
                }
                $itemDueDate = $this->convertJalaliDate($item['due_date'] ?? null);
                if (!empty($itemDueDate)) {
                    if (!empty($data['due_date']) && strtotime($itemDueDate) > strtotime($data['due_date'])) {
                        $taskDueDateJalali = function_exists('jdate') ? jdate(strtotime($data['due_date']))->format('Y/m/d') : $data['due_date'];
                        throw ValidationException::withMessages([
                            'checklist' => "تاریخ سررسید کار «{$item['title']}» نمی‌تواند پس از تاریخ سررسید گروه ({$taskDueDateJalali}) باشد."
                        ]);
                    } elseif ($project->end_date && strtotime($itemDueDate) > strtotime($project->end_date->format('Y-m-d'))) {
                        $projectEndDateJalali = function_exists('jdate') ? jdate($project->end_date)->format('Y/m/d') : $project->end_date->format('Y/m/d');
                        throw ValidationException::withMessages([
                            'checklist' => "تاریخ سررسید کار «{$item['title']}» نمی‌تواند پس از تاریخ پایان پروژه ({$projectEndDateJalali}) باشد."
                        ]);
                    }
                }
                $chkStatusId = !empty($item['is_done'])
                    ? ProjectStatus::completedFor('checklist')?->id
                    : (ProjectStatus::inProgressFor('checklist')?->id ?? ProjectStatus::defaultFor('checklist')?->id);

                $task->checklistItems()->create([
                    'title' => $item['title'],
                    'status_id' => $chkStatusId,
                    'is_done' => !empty($item['is_done']),
                    'due_date' => $itemDueDate,
                    'sort_order' => $i,
                    'completed_at' => !empty($item['is_done']) ? now() : null,
                ]);
            }

            $task->syncStatusFromChecklist();

            return $task;
        });

        ProjectsTaskSseController::broadcastEvent($project->id, 'task_updated', [
            'task_id' => $task->id,
            'action' => 'created',
            'triggered_by_user_id' => auth()->id(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($task->load(['status', 'assignee', 'checklistItems.status']), 201);
        }

        return redirect()->route('projects.projects.show', ['project' => $project->id, 'tab' => 'tasks'])
            ->with('success', "وظیفه «{$task->title}» با موفقیت اضافه شد.");
    }

    public function update(StoreProjectTaskRequest $request, Project $project, ProjectTask $task)
    {
        $this->authorize('editTasks', $project);

        if ($task->isCanceled()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'این گروه لغو شده است و امکان ویرایش آن وجود ندارد.',
                ], 422);
            }

            return redirect()->route('projects.projects.show', ['project' => $project->id, 'tab' => 'tasks'])
                ->with('error', 'این گروه لغو شده است و امکان ویرایش آن وجود ندارد.');
        }

        DB::transaction(function () use ($request, $task, $project) {
            $data = $request->safe()->except('checklist');
            $data['due_date'] = $this->convertJalaliDate($request->input('due_date'));
            $managerId = !empty($data['assigned_to']) ? (int)$data['assigned_to'] : (!empty($data['manager_id']) ? (int)$data['manager_id'] : null);
            if ($managerId && !ProjectsUserVisibilityService::isSuperAdmin(auth()->user())) {
                $targetManager = User::find($managerId);
                if ($targetManager && !ProjectsUserVisibilityService::canViewUser($targetManager, auth()->user())) {
                    throw ValidationException::withMessages([
                        'assigned_to' => 'شما مجاز به انتخاب این کاربر به عنوان مدیر گروه نیستید.'
                    ]);
                }
            }
            $data['manager_id'] = $managerId;
            $data['assigned_to'] = $managerId;

            if ($managerId && !$project->members()->where('user_id', $managerId)->exists()) {
                $project->members()->create(['user_id' => $managerId, 'role' => 'editor']);
            }

            if ($project->end_date && !empty($data['due_date'])) {
                if (strtotime($data['due_date']) > strtotime($project->end_date->format('Y-m-d'))) {
                    $projectEndDateJalali = function_exists('jdate') ? jdate($project->end_date)->format('Y/m/d') : $project->end_date->format('Y/m/d');
                    throw ValidationException::withMessages([
                        'due_date' => "تاریخ سررسید گروه نمی‌تواند پس از تاریخ پایان پروژه ({$projectEndDateJalali}) باشد."
                    ]);
                }
            }

            if (array_key_exists('phase_id', $data) || array_key_exists('group_name', $data)) {
                if (!empty($data['phase_id'])) {
                    $phase = ProjectPhase::find($data['phase_id']);
                    if ($phase) {
                        $data['group_name'] = $phase->name;
                    }
                } elseif (!empty($data['group_name'])) {
                    $phase = $task->project->phases()->firstOrCreate(['name' => trim($data['group_name'])], [
                        'sort_order' => (int)$task->project->phases()->max('sort_order') + 1,
                    ]);
                    $data['phase_id'] = $phase->id;
                    $data['group_name'] = $phase->name;
                } else {
                    $data['phase_id'] = null;
                    $data['group_name'] = null;
                }
            }

            $task->update($data);

            $checklist = $request->input('checklist', []);
            if (is_string($checklist)) {
                $checklist = json_decode($checklist, true) ?? [];
            }
            if (!is_array($checklist)) {
                $checklist = [];
            }

            $submittedIds = [];
            foreach ($checklist as $i => $item) {
                if (empty($item['title'])) {
                    continue;
                }

                $itemDueDate = $this->convertJalaliDate($item['due_date'] ?? null);
                if (!empty($itemDueDate)) {
                    $effectiveTaskDueDate = !empty($data['due_date']) ? $data['due_date'] : ($task->due_date?->format('Y-m-d'));
                    if (!empty($effectiveTaskDueDate) && strtotime($itemDueDate) > strtotime($effectiveTaskDueDate)) {
                        $taskDueDateJalali = function_exists('jdate') ? jdate(strtotime($effectiveTaskDueDate))->format('Y/m/d') : $effectiveTaskDueDate;
                        throw ValidationException::withMessages([
                            'checklist' => "تاریخ سررسید کار «{$item['title']}» نمی‌تواند پس از تاریخ سررسید گروه ({$taskDueDateJalali}) باشد."
                        ]);
                    } elseif ($project->end_date && strtotime($itemDueDate) > strtotime($project->end_date->format('Y-m-d'))) {
                        $projectEndDateJalali = function_exists('jdate') ? jdate($project->end_date)->format('Y/m/d') : $project->end_date->format('Y/m/d');
                        throw ValidationException::withMessages([
                            'checklist' => "تاریخ سررسید کار «{$item['title']}» نمی‌تواند پس از تاریخ پایان پروژه ({$projectEndDateJalali}) باشد."
                        ]);
                    }
                }

                $payload = [
                    'title' => $item['title'],
                    'status_id' => !empty($item['is_done'])
                        ? ProjectStatus::completedFor('checklist')?->id
                        : (ProjectStatus::inProgressFor('checklist')?->id ?? ProjectStatus::defaultFor('checklist')?->id),
                    'is_done' => !empty($item['is_done']),
                    'due_date' => $itemDueDate,
                    'sort_order' => $i,
                    'completed_at' => !empty($item['is_done']) ? ($item['completed_at'] ?? now()) : null,
                ];

                if (!empty($item['id'])) {
                    $existing = $task->checklistItems()->find($item['id']);
                    if ($existing) {
                        $existing->update($payload);
                        $submittedIds[] = $existing->id;
                        continue;
                    }
                }
                $new = $task->checklistItems()->create($payload);
                $submittedIds[] = $new->id;
            }

            if (!empty($submittedIds)) {
                $task->checklistItems()->whereNotIn('id', $submittedIds)->delete();
            } elseif ($request->has('checklist')) {
                $task->checklistItems()->delete();
            }

            $task->syncStatusFromChecklist();
        });

        ProjectsTaskSseController::broadcastEvent($project->id, 'task_updated', [
            'task_id' => $task->id,
            'action' => 'updated',
            'triggered_by_user_id' => auth()->id(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($task->fresh(['status', 'assignee', 'checklistItems.status']));
        }

        return redirect()->route('projects.projects.show', ['project' => $project->id, 'tab' => 'tasks'])
            ->with('success', 'وظیفه با موفقیت ویرایش شد.');
    }

    public function destroy(Project $project, ProjectTask $task)
    {
        $this->authorize('deleteTasks', $project);

        $taskId = $task->id;
        $task->delete();

        ProjectsTaskSseController::broadcastEvent($project->id, 'task_updated', [
            'task_id' => $taskId,
            'action' => 'deleted',
            'triggered_by_user_id' => auth()->id(),
        ]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('projects.projects.show', ['project' => $project->id, 'tab' => 'tasks'])
            ->with('success', 'وظیفه با موفقیت حذف شد.');
    }

    public function bulkDestroy(Request $request, Project $project)
    {
        $this->authorize('deleteTasks', $project);

        if ($project->isCanceled()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'این پروژه لغو شده است و امکان حذف گروه وجود ندارد.'], 422);
            }
            return back()->with('error', 'این پروژه لغو شده است.');
        }

        $validated = $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'integer|exists:projects_tasks,id',
        ]);

        $tasks = $project->tasks()->whereIn('id', $validated['task_ids'])->get();
        $count = $tasks->count();

        if ($count === 0) {
            return response()->json(['success' => false, 'message' => 'هیچ گروهی برای حذف یافت نشد.'], 404);
        }

        $names = $tasks->pluck('title')->implode('، ');

        foreach ($tasks as $task) {
            $task->checklistItems()->delete();
            $task->timeLogs()->delete();
            $task->comments()->delete();
            $task->delete();
        }

        ProjectActivity::log(
            projectId: $project->id,
            action: 'task.deleted',
            subject: "حذف گروهی {$count} گروه («{$names}») از پروژه",
            userId: auth()->id()
        );

        $progressMode = ProjectSetting::get('projects_progress_mode', 'auto_tasks');
        if ($progressMode === 'auto_tasks') {
            $project->refreshProgress();
        }

        ProjectsTaskSseController::broadcastEvent($project->id, 'task_updated', [
            'action' => 'bulk_deleted',
            'triggered_by_user_id' => auth()->id(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "تعداد {$count} گروه با موفقیت حذف شد.",
            ]);
        }

        return redirect()->route('projects.projects.show', ['project' => $project->id, 'tab' => 'tasks'])
            ->with('success', "تعداد {$count} گروه با موفقیت حذف شد.");
    }

    public function reorder(Request $request, Project $project)
    {
        $this->authorize('editTasks', $project);

        $request->validate(['ids' => 'required|array']);

        foreach ($request->ids as $position => $id) {
            ProjectTask::where('id', $id)
                ->where('project_id', $project->id)
                ->update(['sort_order' => $position]);
        }

        ProjectsTaskSseController::broadcastEvent($project->id, 'task_updated', [
            'action' => 'reordered',
            'triggered_by_user_id' => auth()->id(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function updateStatus(Request $request, Project $project, ProjectTask $task)
    {
        if ($request->boolean('from_kanban')) {
            $this->authorize('changeKanbanStatus', $project);
        } else {
            $this->authorize('changeTaskStatus', $project);
        }

        if ($task->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این گروه لغو شده است و تغییر وضعیت آن امکان‌پذیر نیست.',
            ], 422);
        }

        $request->validate([
            'status_id' => 'required|exists:projects_statuses,id',
            'checklist_done_ids' => 'nullable|array',
            'checklist_done_ids.*' => 'integer|exists:projects_checklist_items,id',
        ]);

        $newStatus = ProjectStatus::findOrFail($request->status_id);

        if ($newStatus->isCanceled()) {
            return $this->cancel($project, $task);
        }

        if ($newStatus->isDelayed()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'وضعیت تعویق به صورت خودکار توسط سیستم و تاریخ سررسید مدیریت می‌شود و امکان انتقال دستی به آن وجود ندارد.',
                ], 422);
            }
            return back()->with('error', 'وضعیت تعویق به صورت خودکار توسط سیستم مدیریت می‌شود.');
        }

        if ($task->isDelayed() && !$newStatus->isCompleted() && !$newStatus->isCanceled()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'این گروه در وضعیت تعویق قرار دارد و فقط امکان تغییر وضعیت آن به «تکمیل شده» یا «لغو شده» وجود دارد.',
                ], 422);
            }
            return back()->with('error', 'این گروه در وضعیت تعویق قرار دارد و فقط امکان تغییر وضعیت آن به «تکمیل شده» یا «لغو شده» وجود دارد.');
        }

        try {
            $this->transitionSvc->assertCanTransition($task->status, $newStatus);
        } catch (InvalidStatusTransitionException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        if ($request->has('checklist_done_ids') && is_array($request->checklist_done_ids)) {
            $doneIds = array_map('intval', $request->checklist_done_ids);
            $task->checklistItems()->whereIn('id', $doneIds)->update(['is_done' => true, 'completed_at' => now()]);
            $task->checklistItems()->whereNotIn('id', $doneIds)->update(['is_done' => false, 'completed_at' => null]);
            $task->syncStatusFromChecklist();
        } elseif ($newStatus->isCompleted()) {
            $task->checklistItems()->update(['is_done' => true, 'completed_at' => now()]);
            $task->update([
                'status_id' => $newStatus->id,
                'completed_at' => now(),
            ]);
        } elseif ($newStatus->isQueued()) {
            $task->checklistItems()->update(['is_done' => false, 'completed_at' => null]);
            $task->update([
                'status_id' => $newStatus->id,
                'completed_at' => null,
            ]);
        } else {
            $task->update([
                'status_id' => $newStatus->id,
                'completed_at' => null,
            ]);
        }

        $project->refreshProgress();

        $freshTask = $task->fresh(['status', 'assignee', 'checklistItems']);

        ProjectsTaskSseController::broadcastEvent($project->id, 'task_updated', [
            'task_id' => $task->id,
            'status_id' => $newStatus->id,
            'task_status' => [
                'id' => $newStatus->id,
                'name' => $newStatus->name,
                'color' => $newStatus->color,
                'is_completed' => $newStatus->isCompleted(),
            ],
            'task_progress' => $task->checklistProgress(),
            'checklist_done' => $task->completedChecklistCount(),
            'checklist_total' => $task->totalChecklistCount(),
            'project_progress' => $project->progress,
            'action' => 'status_updated',
            'triggered_by_user_id' => auth()->id(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'task' => $freshTask,
                'task_progress' => $task->checklistProgress(),
                'checklist_done' => $task->completedChecklistCount(),
                'checklist_total' => $task->totalChecklistCount(),
                'project_progress' => $project->progress,
                'dashboard_stats' => $this->projectsSvc->dashboardStats($project->fresh()),
            ]);
        }

        return back()->with('success', 'وضعیت کار به‌روز شد.');
    }

    public function cancel(Project $project, ProjectTask $task)
    {
        $this->authorize('cancelTasks', $project);

        $canceledStatus = ProjectStatus::canceledFor('task');
        if (!$canceledStatus) {
            return response()->json(['success' => false, 'message' => 'وضعیت «لغو شده» برای کارها یافت نشد.'], 422);
        }

        $task->update([
            'status_id' => $canceledStatus->id,
            'completed_at' => null,
        ]);

        ProjectActivity::log(
            projectId: $project->id,
            action: 'task.canceled',
            subject: "لغو گروه «{$task->title}»",
            taskId: $task->id,
            userId: auth()->id()
        );

        $project->refreshProgress();

        ProjectsTaskSseController::broadcastEvent($project->id, 'task_updated', [
            'task_id' => $task->id,
            'action' => 'canceled',
            'triggered_by_user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'task' => $task->fresh(['status', 'assignee']),
            'dashboard_stats' => $this->projectsSvc->dashboardStats($project->fresh()),
        ]);
    }

    /**
     * Import tasks and phases directly into a project from a JSON file or JSON payload.
     */
    public function importJson(Request $request, Project $project)
    {
        $this->authorize('createTasks', $project);

        if ($project->isCanceled()) {
            return response()->json([
                'success' => false,
                'message' => 'این پروژه لغو شده است و امکان افزودن کار به آن وجود ندارد.'
            ], 422);
        }

        $request->validate([
            'json_file' => 'nullable|file|max:10240',
            'json_content' => 'nullable|string',
            'structure' => 'nullable',
            'import_mode' => 'nullable|in:append,replace',
        ], [
            'json_file.file' => 'فایل انتخاب‌شده نامعتبر است.',
            'json_file.max' => 'حجم فایل نمی‌تواند بیشتر از ۱۰ مگابایت باشد.',
        ]);

        $rawJson = '';
        if ($request->hasFile('json_file')) {
            $rawJson = file_get_contents($request->file('json_file')->getRealPath());
        } elseif ($request->filled('json_content')) {
            $rawJson = $request->input('json_content');
        } elseif ($request->filled('structure')) {
            $rawJson = is_array($request->input('structure')) ? json_encode($request->input('structure')) : $request->input('structure');
        }

        if (empty($rawJson)) {
            return response()->json([
                'success' => false,
                'message' => 'لطفاً فایل JSON را انتخاب کنید یا محتوای JSON را وارد نمایید.'
            ], 422);
        }

        // Strip UTF-8 BOM if present
        $rawJson = preg_replace('/^\xEF\xBB\xBF/', '', trim($rawJson));
        $decoded = json_decode($rawJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return response()->json([
                'success' => false,
                'message' => 'فرمت فایل JSON نامعتبر است یا ساختار آن آسیب دیده است: ' . json_last_error_msg()
            ], 422);
        }

        $importMode = $request->input('import_mode', 'append');
        $structure = $this->extractAndNormalizeJsonStructure($decoded);

        $resultCounts = DB::transaction(function () use ($project, $structure, $importMode) {
            if ($importMode === 'replace') {
                $tasks = $project->tasks()->get();
                foreach ($tasks as $task) {
                    $task->checklistItems()->delete();
                    $task->timeLogs()->delete();
                    $task->comments()->delete();
                    $task->delete();
                }
                $project->phases()->delete();
            }

            $defaultTaskStatus = ProjectStatus::defaultFor('task')?->id
                ?? ProjectStatus::queuedFor('task')?->id
                ?? ProjectStatus::forType('task')->first()?->id;

            $defaultChecklistStatus = ProjectStatus::defaultFor('checklist')?->id
                ?? ProjectStatus::inProgressFor('checklist')?->id
                ?? ProjectStatus::queuedFor('checklist')?->id
                ?? ProjectStatus::forType('checklist')->first()?->id;

            $maxPhaseOrder = (int)$project->phases()->max('sort_order');
            $maxTaskOrder = (int)$project->tasks()->max('sort_order');

            $resolveDueDate = function(?string $rawDate) use ($project) {
                if (empty($rawDate)) return null;
                $rawDate = trim((string)$rawDate);
                if (is_numeric($rawDate)) {
                    $base = $project->start_date ? \Carbon\Carbon::parse($project->start_date) : now();
                    return $base->copy()->addDays((int)$rawDate)->format('Y-m-d');
                }
                return $this->convertJalaliDate($rawDate);
            };

            $createdPhasesCount = 0;
            $createdTasksCount = 0;
            $createdItemsCount = 0;

            // 1. Phases & Phase Tasks
            foreach ($structure['phases'] ?? [] as $phaseData) {
                $phaseName = trim($phaseData['name'] ?? $phaseData['title'] ?? '');
                if (empty($phaseName)) continue;

                $phase = null;
                if ($importMode === 'append') {
                    $phase = $project->phases()->where('name', $phaseName)->first();
                }

                if (!$phase) {
                    $maxPhaseOrder++;
                    $phase = $project->phases()->create([
                        'name' => $phaseName,
                        'color' => $phaseData['color'] ?? '#6366f1',
                        'description' => $phaseData['description'] ?? null,
                        'sort_order' => $maxPhaseOrder,
                    ]);
                    $createdPhasesCount++;
                }

                foreach ($phaseData['tasks'] ?? [] as $taskData) {
                    $taskTitle = trim($taskData['title'] ?? $taskData['name'] ?? '');
                    if (empty($taskTitle)) continue;

                    $maxTaskOrder++;
                    $managerId = !empty($taskData['manager_id']) ? (int)$taskData['manager_id'] : (!empty($taskData['assigned_to']) ? (int)$taskData['assigned_to'] : null);
                    $rawDueDate = !empty($taskData['due_date']) ? $taskData['due_date'] : null;
                    $taskDueDate = $resolveDueDate($rawDueDate);

                    if ($managerId && !$project->members()->where('user_id', $managerId)->exists()) {
                        $project->members()->create(['user_id' => $managerId, 'role' => 'editor']);
                    }

                    $task = $project->tasks()->create([
                        'phase_id' => $phase->id,
                        'group_name' => $phase->name,
                        'title' => $taskTitle,
                        'description' => $taskData['description'] ?? null,
                        'status_id' => $defaultTaskStatus,
                        'manager_id' => $managerId,
                        'assigned_to' => $managerId,
                        'due_date' => $taskDueDate,
                        'created_by' => auth()->id(),
                        'sort_order' => $maxTaskOrder,
                    ]);
                    $createdTasksCount++;

                    $items = $taskData['items'] ?? $taskData['checklist'] ?? $taskData['subtasks'] ?? [];
                    foreach ($items as $iIndex => $itemData) {
                        $itemTitle = trim(is_string($itemData) ? $itemData : ($itemData['title'] ?? $itemData['name'] ?? ''));
                        if (empty($itemTitle)) continue;

                        $itemAssignee = is_array($itemData) ? (!empty($itemData['assigned_to']) ? (int)$itemData['assigned_to'] : $managerId) : $managerId;
                        $rawItemDueDate = is_array($itemData) ? (!empty($itemData['due_date']) ? $itemData['due_date'] : $rawDueDate) : $rawDueDate;
                        $itemDueDate = $rawItemDueDate ? $resolveDueDate($rawItemDueDate) : $taskDueDate;

                        if ($itemAssignee && !$project->members()->where('user_id', $itemAssignee)->exists()) {
                            $project->members()->create(['user_id' => $itemAssignee, 'role' => 'editor']);
                        }

                        $chkItem = $task->checklistItems()->create([
                            'title' => $itemTitle,
                            'description' => is_array($itemData) ? ($itemData['description'] ?? null) : null,
                            'status_id' => $defaultChecklistStatus,
                            'assigned_to' => $itemAssignee,
                            'due_date' => $itemDueDate,
                            'created_by' => auth()->id(),
                            'is_done' => false,
                            'sort_order' => $iIndex + 1,
                        ]);
                        $createdItemsCount++;

                        if ($itemAssignee) {
                            $chkItem->syncAssignees([$itemAssignee]);
                        }
                    }

                    $task->syncStatusFromChecklist();
                }
            }

            // 2. Unphased Tasks
            foreach ($structure['unphased_tasks'] ?? [] as $taskData) {
                $taskTitle = trim($taskData['title'] ?? $taskData['name'] ?? '');
                if (empty($taskTitle)) continue;

                $maxTaskOrder++;
                $managerId = !empty($taskData['manager_id']) ? (int)$taskData['manager_id'] : (!empty($taskData['assigned_to']) ? (int)$taskData['assigned_to'] : null);
                $rawDueDate = !empty($taskData['due_date']) ? $taskData['due_date'] : null;
                $taskDueDate = $resolveDueDate($rawDueDate);

                if ($managerId && !$project->members()->where('user_id', $managerId)->exists()) {
                    $project->members()->create(['user_id' => $managerId, 'role' => 'editor']);
                }

                $task = $project->tasks()->create([
                    'phase_id' => null,
                    'group_name' => null,
                    'title' => $taskTitle,
                    'description' => $taskData['description'] ?? null,
                    'status_id' => $defaultTaskStatus,
                    'manager_id' => $managerId,
                    'assigned_to' => $managerId,
                    'due_date' => $taskDueDate,
                    'created_by' => auth()->id(),
                    'sort_order' => $maxTaskOrder,
                ]);
                $createdTasksCount++;

                $items = $taskData['items'] ?? $taskData['checklist'] ?? $taskData['subtasks'] ?? [];
                foreach ($items as $iIndex => $itemData) {
                    $itemTitle = trim(is_string($itemData) ? $itemData : ($itemData['title'] ?? $itemData['name'] ?? ''));
                    if (empty($itemTitle)) continue;

                    $itemAssignee = is_array($itemData) ? (!empty($itemData['assigned_to']) ? (int)$itemData['assigned_to'] : $managerId) : $managerId;
                    $rawItemDueDate = is_array($itemData) ? (!empty($itemData['due_date']) ? $itemData['due_date'] : $rawDueDate) : $rawDueDate;
                    $itemDueDate = $rawItemDueDate ? $resolveDueDate($rawItemDueDate) : $taskDueDate;

                    if ($itemAssignee && !$project->members()->where('user_id', $itemAssignee)->exists()) {
                        $project->members()->create(['user_id' => $itemAssignee, 'role' => 'editor']);
                    }

                    $chkItem = $task->checklistItems()->create([
                        'title' => $itemTitle,
                        'description' => is_array($itemData) ? ($itemData['description'] ?? null) : null,
                        'status_id' => $defaultChecklistStatus,
                        'assigned_to' => $itemAssignee,
                        'due_date' => $itemDueDate,
                        'created_by' => auth()->id(),
                        'is_done' => false,
                        'sort_order' => $iIndex + 1,
                    ]);
                    $createdItemsCount++;

                    if ($itemAssignee) {
                        $chkItem->syncAssignees([$itemAssignee]);
                    }
                }

                $task->syncStatusFromChecklist();
            }

            ProjectActivity::log(
                projectId: $project->id,
                action: 'project.updated',
                subject: "درون‌ریزی ساختار کارها از فایل JSON ({$createdTasksCount} گروه و {$createdItemsCount} کار)",
                userId: auth()->id()
            );

            $project->refreshProgress();

            return [
                'phases' => $createdPhasesCount,
                'tasks' => $createdTasksCount,
                'items' => $createdItemsCount,
            ];
        });

        ProjectsTaskSseController::broadcastEvent($project->id, 'checklist_updated', [
            'action' => 'json_imported',
            'triggered_by_user_id' => auth()->id(),
        ]);

        $msgParts = [];
        if ($resultCounts['phases'] > 0) $msgParts[] = "{$resultCounts['phases']} فاز";
        if ($resultCounts['tasks'] > 0) $msgParts[] = "{$resultCounts['tasks']} گروه";
        if ($resultCounts['items'] > 0) $msgParts[] = "{$resultCounts['items']} کار";

        $summaryText = !empty($msgParts) ? implode('، ', $msgParts) : 'اطلاعات';

        return response()->json([
            'success' => true,
            'message' => "{$summaryText} با موفقیت به پروژه اضافه شد.",
            'counts' => $resultCounts,
        ]);
    }

    /**
     * Parse and normalize arbitrary JSON structure into standard phases and unphased tasks.
     */
    private function extractAndNormalizeJsonStructure(array $data): array
    {
        $structure = ['phases' => [], 'unphased_tasks' => []];

        if (isset($data['structure']) && is_array($data['structure'])) {
            $rawStructure = $data['structure'];
            $structure['phases'] = $rawStructure['phases'] ?? $rawStructure['فازها'] ?? [];
            $structure['unphased_tasks'] = $rawStructure['unphased_tasks'] ?? $rawStructure['گروه‌های عمومی'] ?? $rawStructure['tasks'] ?? $rawStructure['گروه‌ها'] ?? [];
        } elseif (isset($data['phases']) || isset($data['فازها'])) {
            $structure['phases'] = $data['phases'] ?? $data['فازها'] ?? [];
            $structure['unphased_tasks'] = $data['unphased_tasks'] ?? $data['tasks'] ?? $data['گروه‌ها'] ?? [];
        } elseif (array_is_list($data)) {
            $hasPhases = false;
            foreach ($data as $item) {
                if (is_array($item) && (isset($item['tasks']) || isset($item['فاز']) || isset($item['phases']) || isset($item['کارها']))) {
                    $hasPhases = true;
                    break;
                }
            }
            if ($hasPhases) {
                $structure['phases'] = $data;
            } else {
                $structure['unphased_tasks'] = $data;
            }
        } elseif (isset($data['tasks']) || isset($data['گروه‌ها']) || isset($data['کارها'])) {
            $structure['unphased_tasks'] = $data['tasks'] ?? $data['گروه‌ها'] ?? $data['کارها'] ?? [];
        }

        $colors = ['#6366f1', '#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899'];
        $cleanPhases = [];
        foreach ($structure['phases'] as $idx => $p) {
            if (!is_array($p)) continue;
            $phaseName = trim($p['name'] ?? $p['title'] ?? $p['نام'] ?? $p['عنوان'] ?? "فاز " . ($idx + 1));
            $tasksList = $p['tasks'] ?? $p['گروه‌ها'] ?? $p['کارها'] ?? $p['items'] ?? [];
            $cleanTasks = [];

            foreach ($tasksList as $t) {
                if (!is_array($t)) continue;
                $tTitle = trim($t['title'] ?? $t['name'] ?? $t['عنوان'] ?? $t['نام'] ?? '');
                $tItems = $t['items'] ?? $t['checklist'] ?? $t['کارها'] ?? $t['چک‌لیست'] ?? $t['subtasks'] ?? [];
                if (empty($tTitle) && empty($tItems)) continue;

                $cleanItems = [];
                foreach ($tItems as $i) {
                    $iTitle = trim(is_string($i) ? $i : ($i['title'] ?? $i['name'] ?? $i['عنوان'] ?? ''));
                    if (empty($iTitle)) continue;

                    $cleanItems[] = [
                        'title' => $iTitle,
                        'description' => is_array($i) ? trim($i['description'] ?? $i['توضیحات'] ?? '') : '',
                        'assigned_to' => is_array($i) ? ($i['assigned_to'] ?? $i['مسئول'] ?? '') : '',
                        'due_date' => is_array($i) ? ($i['due_date'] ?? $i['مهلت'] ?? '') : '',
                    ];
                }

                $cleanTasks[] = [
                    'title' => $tTitle ?: 'گروه کاری ' . (count($cleanTasks) + 1),
                    'description' => trim($t['description'] ?? $t['توضیحات'] ?? ''),
                    'manager_id' => $t['manager_id'] ?? $t['assigned_to'] ?? $t['مسئول'] ?? '',
                    'due_date' => $t['due_date'] ?? $t['مهلت'] ?? '',
                    'items' => $cleanItems,
                ];
            }

            $cleanPhases[] = [
                'name' => $phaseName,
                'color' => $p['color'] ?? $p['رنگ'] ?? $colors[$idx % count($colors)],
                'description' => trim($p['description'] ?? $p['توضیحات'] ?? ''),
                'tasks' => $cleanTasks,
            ];
        }

        $cleanUnphased = [];
        foreach ($structure['unphased_tasks'] as $t) {
            if (!is_array($t)) continue;
            $tTitle = trim($t['title'] ?? $t['name'] ?? $t['عنوان'] ?? $t['نام'] ?? '');
            $tItems = $t['items'] ?? $t['checklist'] ?? $t['کارها'] ?? $t['چک‌لیست'] ?? $t['subtasks'] ?? [];
            if (empty($tTitle) && empty($tItems)) continue;

            $cleanItems = [];
            foreach ($tItems as $i) {
                $iTitle = trim(is_string($i) ? $i : ($i['title'] ?? $i['name'] ?? $i['عنوان'] ?? ''));
                if (empty($iTitle)) continue;

                $cleanItems[] = [
                    'title' => $iTitle,
                    'description' => is_array($i) ? trim($i['description'] ?? $i['توضیحات'] ?? '') : '',
                    'assigned_to' => is_array($i) ? ($i['assigned_to'] ?? $i['مسئول'] ?? '') : '',
                    'due_date' => is_array($i) ? ($i['due_date'] ?? $i['مهلت'] ?? '') : '',
                ];
            }

            $cleanUnphased[] = [
                'title' => $tTitle ?: 'گروه کاری ' . (count($cleanUnphased) + 1),
                'description' => trim($t['description'] ?? $t['توضیحات'] ?? ''),
                'manager_id' => $t['manager_id'] ?? $t['assigned_to'] ?? $t['مسئول'] ?? '',
                'due_date' => $t['due_date'] ?? $t['مهلت'] ?? '',
                'items' => $cleanItems,
            ];
        }

        return [
            'phases' => $cleanPhases,
            'unphased_tasks' => $cleanUnphased,
        ];
    }
}

