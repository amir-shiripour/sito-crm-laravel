<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectActivity;
use Modules\Projects\App\Http\Models\ProjectCategory;
use Modules\Projects\App\Http\Models\ProjectPhase;
use Modules\Projects\App\Http\Models\ProjectStatus;
use Modules\Projects\App\Http\Models\ProjectTask;
use Modules\Projects\App\Http\Models\ProjectTemplate;
use Modules\Projects\App\Http\Requests\StoreProjectTemplateRequest;
use Modules\Projects\App\Traits\HandlesJalaliDate;

class ProjectsTemplateController extends Controller
{
    use HandlesJalaliDate;

    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('projects.templates.view'), 403);

        $query = ProjectTemplate::with(['category', 'creator'])
            ->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $templates = $query->paginate(12)->withQueryString();
        $categories = ProjectCategory::active()->ordered()->get();

        // Calculate statistics
        $allTemplates = ProjectTemplate::all();
        $stats = [
            'total_templates' => $allTemplates->count(),
            'total_phases' => $allTemplates->sum('phases_count'),
            'total_tasks' => $allTemplates->sum('tasks_count'),
            'total_items' => $allTemplates->sum('items_count'),
        ];

        return view('projects::templates.index', compact('templates', 'categories', 'stats'));
    }

    public function create(Request $request)
    {
        abort_unless(auth()->user()?->can('projects.templates.create'), 403);

        $categories = ProjectCategory::active()->ordered()->get();
        $users = User::select('id', 'name')->orderBy('name')->get();

        $initialData = [
            'title' => '',
            'description' => '',
            'category_id' => '',
            'structure' => [
                'phases' => [],
                'unphased_tasks' => [],
            ],
        ];

        $fromProject = null;
        if ($request->filled('from_project')) {
            $fromProject = Project::with([
                'phases' => fn($q) => $q->orderBy('sort_order'),
                'tasks' => fn($q) => $q->orderBy('sort_order')->with('checklistItems'),
            ])->find($request->from_project);

            if ($fromProject) {
                $initialData['title'] = "الگوی ساختار پروژه {$fromProject->title}";
                $initialData['description'] = "الگوی استخراج‌شده از فازها و کارهای پروژه «{$fromProject->title}»";
                $initialData['category_id'] = $fromProject->category_id ? (string)$fromProject->category_id : '';

                $phasesList = [];
                foreach ($fromProject->phases as $phase) {
                    $phaseTasks = [];
                    foreach ($fromProject->tasks->where('phase_id', $phase->id) as $task) {
                        $taskManagerId = ($task->manager_id ?? $task->assigned_to) ? (string)($task->manager_id ?? $task->assigned_to) : '';
                        $taskDueDate = $this->formatTemplateDueDate($task->due_date_jalali ?: ($task->due_date ? (function_exists('jdate') ? jdate($task->due_date)->format('Y/m/d') : $task->due_date->format('Y/m/d')) : ''));

                        $itemsList = [];
                        foreach ($task->checklistItems as $item) {
                            $itemDueDate = $this->formatTemplateDueDate($item->due_date_jalali ?: ($item->due_date ? (function_exists('jdate') ? jdate($item->due_date)->format('Y/m/d') : $item->due_date->format('Y/m/d')) : $taskDueDate));
                            $itemAssignee = $item->assigned_to ? (string)$item->assigned_to : $taskManagerId;

                            $itemsList[] = [
                                'title' => $item->title,
                                'description' => $item->description ?? '',
                                'assigned_to' => $itemAssignee,
                                'due_date' => $itemDueDate,
                            ];
                        }

                        $phaseTasks[] = [
                            'title' => $task->title,
                            'description' => $task->description ?? '',
                            'manager_id' => $taskManagerId,
                            'due_date' => $taskDueDate,
                            'items' => $itemsList,
                        ];
                    }

                    $phasesList[] = [
                        'name' => $phase->name,
                        'color' => $phase->color ?? '#6366f1',
                        'description' => $phase->description ?? '',
                        'tasks' => $phaseTasks,
                    ];
                }

                $unphasedList = [];
                foreach ($fromProject->tasks->whereNull('phase_id') as $task) {
                    $taskManagerId = ($task->manager_id ?? $task->assigned_to) ? (string)($task->manager_id ?? $task->assigned_to) : '';
                    $taskDueDate = $this->formatTemplateDueDate($task->due_date_jalali ?: ($task->due_date ? (function_exists('jdate') ? jdate($task->due_date)->format('Y/m/d') : $task->due_date->format('Y/m/d')) : ''));

                    $itemsList = [];
                    foreach ($task->checklistItems as $item) {
                        $itemDueDate = $this->formatTemplateDueDate($item->due_date_jalali ?: ($item->due_date ? (function_exists('jdate') ? jdate($item->due_date)->format('Y/m/d') : $item->due_date->format('Y/m/d')) : $taskDueDate));
                        $itemAssignee = $item->assigned_to ? (string)$item->assigned_to : $taskManagerId;

                        $itemsList[] = [
                            'title' => $item->title,
                            'description' => $item->description ?? '',
                            'assigned_to' => $itemAssignee,
                            'due_date' => $itemDueDate,
                        ];
                    }

                    $unphasedList[] = [
                        'title' => $task->title,
                        'description' => $task->description ?? '',
                        'manager_id' => $taskManagerId,
                        'due_date' => $taskDueDate,
                        'items' => $itemsList,
                    ];
                }

                $initialData['structure'] = [
                    'phases' => $phasesList,
                    'unphased_tasks' => $unphasedList,
                ];
            }
        }

        return view('projects::templates.create', compact('categories', 'initialData', 'fromProject', 'users'));
    }

    public function store(StoreProjectTemplateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $fromProject = !empty($validated['source_project_id'])
            ? Project::with(['phases.tasks.checklistItems', 'tasks.checklistItems'])->find($validated['source_project_id'])
            : null;

        $rawStructure = $request->input('structure', []);
        $structure = $this->normalizeStructure($rawStructure, $fromProject);

        $template = ProjectTemplate::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'source_project_id' => $validated['source_project_id'] ?? null,
            'structure' => $structure,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('projects.templates.index')
            ->with('success', "الگوی «{$template->title}» با موفقیت ایجاد شد.");
    }

    public function show(ProjectTemplate $template)
    {
        abort_unless(auth()->user()?->can('projects.templates.view'), 403);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($template->load(['category', 'creator']));
        }

        $users = User::select('id', 'name')->get()->keyBy('id');

        $sourceProject = $template->sourceProject ? $template->sourceProject->load(['phases.tasks.checklistItems', 'tasks.checklistItems']) : null;
        $structure = $this->normalizeStructure($template->structure, $sourceProject);

        return view('projects::templates.show', compact('template', 'users', 'structure'));
    }

    public function edit(ProjectTemplate $template)
    {
        abort_unless(auth()->user()?->can('projects.templates.edit'), 403);

        $categories = ProjectCategory::active()->ordered()->get();
        $users = User::select('id', 'name')->orderBy('name')->get();

        $sourceProject = $template->sourceProject ? $template->sourceProject->load(['phases.tasks.checklistItems', 'tasks.checklistItems']) : null;
        $structure = $this->normalizeStructure($template->structure, $sourceProject);

        $initialData = [
            'title' => $template->title,
            'description' => $template->description ?? '',
            'category_id' => $template->category_id ? (string)$template->category_id : '',
            'structure' => $structure,
        ];

        return view('projects::templates.edit', compact('template', 'categories', 'users', 'initialData'));
    }

    public function update(StoreProjectTemplateRequest $request, ProjectTemplate $template): RedirectResponse
    {
        $validated = $request->validated();

        $sourceProject = $template->sourceProject ? $template->sourceProject->load(['phases.tasks.checklistItems', 'tasks.checklistItems']) : null;
        $rawStructure = $request->input('structure', []);
        $structure = $this->normalizeStructure($rawStructure, $sourceProject);

        $template->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'structure' => $structure,
        ]);

        return redirect()->route('projects.templates.index')
            ->with('success', "الگوی «{$template->title}» با موفقیت ویرایش شد.");
    }

    public function destroy(ProjectTemplate $template): RedirectResponse
    {
        abort_unless(auth()->user()?->can('projects.templates.delete'), 403);

        $title = $template->title;
        $template->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('projects.templates.index')
            ->with('success', "الگوی «{$title}» با موفقیت حذف شد.");
    }

    private function normalizeStructure(mixed $structure, ?Project $sourceProject = null): array
    {
        if (is_string($structure)) {
            $structure = json_decode($structure, true) ?? [];
        }
        if (!is_array($structure)) {
            $structure = ['phases' => [], 'unphased_tasks' => []];
        }

        $sourceTasksMap = $sourceProject ? $sourceProject->tasks->keyBy('title') : collect();

        $rawPhases = $structure['phases'] ?? $structure['فازها'] ?? [];
        $rawUnphased = $structure['unphased_tasks'] ?? $structure['tasks'] ?? $structure['کارها'] ?? $structure['گروه‌ها'] ?? [];

        $normalizedPhases = [];
        foreach ($rawPhases as $phase) {
            $phaseName = trim($phase['name'] ?? $phase['title'] ?? $phase['نام'] ?? $phase['عنوان'] ?? '');
            $phaseTasks = [];
            $tasksList = $phase['tasks'] ?? $phase['کارها'] ?? $phase['تسک‌ها'] ?? $phase['groups'] ?? [];

            foreach ($tasksList as $task) {
                $taskTitle = trim($task['title'] ?? $task['name'] ?? $task['عنوان'] ?? $task['نام'] ?? '');
                $taskItems = $task['items'] ?? $task['subtasks'] ?? $task['چک‌لیست'] ?? $task['آیتم‌ها'] ?? $task['زیرکارها'] ?? [];

                if (empty($taskTitle) && empty($taskItems)) {
                    continue;
                }

                $srcTask = $sourceTasksMap->get($taskTitle);

                $taskManagerId = ($task['manager_id'] ?? $task['assigned_to'] ?? $task['مسئول'] ?? '')
                    ?: ($srcTask ? (($srcTask->manager_id ?? $srcTask->assigned_to) ? (string)($srcTask->manager_id ?? $srcTask->assigned_to) : '') : '');
                $rawTaskDueDate = $task['due_date'] ?? $task['مهلت'] ?? ($srcTask ? ($srcTask->due_date_jalali ?: ($srcTask->due_date ? (function_exists('jdate') ? jdate($srcTask->due_date)->format('Y/m/d') : $srcTask->due_date->format('Y/m/d')) : '')) : '');
                $taskDueDate = $this->formatTemplateDueDate($rawTaskDueDate);

                $srcItemsMap = $srcTask ? $srcTask->checklistItems->keyBy('title') : collect();

                $items = [];
                foreach ($taskItems as $item) {
                    $itemTitle = trim(is_string($item) ? $item : ($item['title'] ?? $item['name'] ?? $item['عنوان'] ?? ''));
                    if (empty($itemTitle)) {
                        continue;
                    }

                    $srcItem = $srcItemsMap->get($itemTitle);

                    $itemAssignee = is_array($item) ? ($item['assigned_to'] ?? $item['مسئول'] ?? '') : '';
                    $itemAssignee = $itemAssignee ?: ($srcItem ? ($srcItem->assigned_to ? (string)$srcItem->assigned_to : $taskManagerId) : $taskManagerId);

                    $rawItemDueDate = is_array($item) ? ($item['due_date'] ?? $item['مهلت'] ?? '') : '';
                    $itemDueDate = $this->formatTemplateDueDate($rawItemDueDate ?: ($srcItem ? ($srcItem->due_date_jalali ?: ($srcItem->due_date ? (function_exists('jdate') ? jdate($srcItem->due_date)->format('Y/m/d') : $srcItem->due_date->format('Y/m/d')) : $taskDueDate)) : $taskDueDate));

                    $items[] = [
                        'title' => $itemTitle,
                        'description' => is_array($item) ? trim($item['description'] ?? $item['توضیحات'] ?? '') : '',
                        'assigned_to' => (string)$itemAssignee,
                        'due_date' => (string)$itemDueDate,
                    ];
                }

                $phaseTasks[] = [
                    'title' => $taskTitle,
                    'description' => trim($task['description'] ?? $task['توضیحات'] ?? ''),
                    'manager_id' => (string)$taskManagerId,
                    'due_date' => (string)$taskDueDate,
                    'items' => $items,
                ];
            }

            if (!empty($phaseName) || !empty($phaseTasks)) {
                $normalizedPhases[] = [
                    'name' => $phaseName,
                    'color' => $phase['color'] ?? $phase['رنگ'] ?? '#6366f1',
                    'description' => trim($phase['description'] ?? $phase['توضیحات'] ?? ''),
                    'tasks' => $phaseTasks,
                ];
            }
        }

        $normalizedUnphased = [];
        foreach ($rawUnphased as $task) {
            $taskTitle = trim($task['title'] ?? $task['name'] ?? $task['عنوان'] ?? $task['نام'] ?? '');
            $taskItems = $task['items'] ?? $task['subtasks'] ?? $task['چک‌لیست'] ?? $task['آیتم‌ها'] ?? $task['زیرکارها'] ?? [];

            if (empty($taskTitle) && empty($taskItems)) {
                continue;
            }

            $srcTask = $sourceTasksMap->get($taskTitle);

            $taskManagerId = ($task['manager_id'] ?? $task['assigned_to'] ?? $task['مسئول'] ?? '')
                ?: ($srcTask ? (($srcTask->manager_id ?? $srcTask->assigned_to) ? (string)($srcTask->manager_id ?? $srcTask->assigned_to) : '') : '');
            $rawTaskDueDate = $task['due_date'] ?? $task['مهلت'] ?? ($srcTask ? ($srcTask->due_date_jalali ?: ($srcTask->due_date ? (function_exists('jdate') ? jdate($srcTask->due_date)->format('Y/m/d') : $srcTask->due_date->format('Y/m/d')) : '')) : '');
            $taskDueDate = $this->formatTemplateDueDate($rawTaskDueDate);

            $srcItemsMap = $srcTask ? $srcTask->checklistItems->keyBy('title') : collect();

            $items = [];
            foreach ($taskItems as $item) {
                $itemTitle = trim(is_string($item) ? $item : ($item['title'] ?? $item['name'] ?? $item['عنوان'] ?? ''));
                if (empty($itemTitle)) {
                    continue;
                }

                $srcItem = $srcItemsMap->get($itemTitle);

                $itemAssignee = is_array($item) ? ($item['assigned_to'] ?? $item['مسئول'] ?? '') : '';
                $itemAssignee = $itemAssignee ?: ($srcItem ? ($srcItem->assigned_to ? (string)$srcItem->assigned_to : $taskManagerId) : $taskManagerId);

                $rawItemDueDate = is_array($item) ? ($item['due_date'] ?? $item['مهلت'] ?? '') : '';
                $itemDueDate = $this->formatTemplateDueDate($rawItemDueDate ?: ($srcItem ? ($srcItem->due_date_jalali ?: ($srcItem->due_date ? (function_exists('jdate') ? jdate($srcItem->due_date)->format('Y/m/d') : $srcItem->due_date->format('Y/m/d')) : $taskDueDate)) : $taskDueDate));

                $items[] = [
                    'title' => $itemTitle,
                    'description' => is_array($item) ? trim($item['description'] ?? $item['توضیحات'] ?? '') : '',
                    'assigned_to' => (string)$itemAssignee,
                    'due_date' => (string)$itemDueDate,
                ];
            }

            $normalizedUnphased[] = [
                'title' => $taskTitle,
                'description' => trim($task['description'] ?? $task['توضیحات'] ?? ''),
                'manager_id' => (string)$taskManagerId,
                'due_date' => (string)$taskDueDate,
                'items' => $items,
            ];
        }

        return [
            'phases' => $normalizedPhases,
            'unphased_tasks' => $normalizedUnphased,
        ];
    }

    private function formatTemplateDueDate(mixed $dueDate): string
    {
        if (empty($dueDate)) {
            return '';
        }

        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $latin = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $dueDate = str_replace($persian, $latin, trim((string)$dueDate));

        if (empty($dueDate)) {
            return '';
        }

        if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})/', $dueDate, $matches)) {
            return sprintf('%04d/%02d/%02d', (int)$matches[1], (int)$matches[2], (int)$matches[3]);
        }

        if (is_numeric($dueDate)) {
            $days = (int)$dueDate;
            try {
                if (class_exists(\Morilog\Jalali\Jalalian::class)) {
                    return \Morilog\Jalali\Jalalian::now()->addDays($days)->format('Y/m/d');
                }
                if (function_exists('jdate')) {
                    return jdate()->addDays($days)->format('Y/m/d');
                }
            } catch (\Throwable) {
                return '';
            }
        }

        return $dueDate;
    }

    public function apply(Request $request, ProjectTemplate $template, Project $project)
    {
        $this->authorize('applyTemplates', $project);

        if ($project->isCanceled()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'این پروژه لغو شده است و امکان اعمال الگو در آن وجود ندارد.'], 422);
            }
            return back()->with('error', 'این پروژه لغو شده است.');
        }

        DB::transaction(function () use ($template, $project) {
            $sourceProject = $template->sourceProject ? $template->sourceProject->load(['phases.tasks.checklistItems', 'tasks.checklistItems']) : null;
            $structure = $this->normalizeStructure($template->structure, $sourceProject);
            $phases = $structure['phases'] ?? [];
            $unphasedTasks = $structure['unphased_tasks'] ?? [];

            $defaultTaskStatus = ProjectStatus::defaultFor('task')?->id
                ?? ProjectStatus::queuedFor('task')?->id
                ?? ProjectStatus::forType('task')->first()?->id;

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

            // 1. Create Phases & their Tasks
            foreach ($phases as $phaseData) {
                if (empty($phaseData['name'])) continue;

                $maxPhaseOrder++;
                $phase = $project->phases()->create([
                    'name' => trim($phaseData['name']),
                    'color' => $phaseData['color'] ?? '#6366f1',
                    'description' => $phaseData['description'] ?? null,
                    'sort_order' => $maxPhaseOrder,
                ]);

                foreach ($phaseData['tasks'] ?? [] as $taskData) {
                    if (empty($taskData['title'])) continue;

                    $maxTaskOrder++;
                    $managerId = !empty($taskData['manager_id']) ? (int)$taskData['manager_id'] : null;
                    $rawDueDate = !empty($taskData['due_date']) ? $taskData['due_date'] : null;
                    $taskDueDate = $resolveDueDate($rawDueDate);

                    if ($managerId && !$project->members()->where('user_id', $managerId)->exists()) {
                        $project->members()->create(['user_id' => $managerId, 'role' => 'editor']);
                    }

                    $task = $project->tasks()->create([
                        'phase_id' => $phase->id,
                        'group_name' => $phase->name,
                        'title' => trim($taskData['title']),
                        'description' => $taskData['description'] ?? null,
                        'status_id' => $defaultTaskStatus,
                        'manager_id' => $managerId,
                        'assigned_to' => $managerId,
                        'due_date' => $taskDueDate,
                        'created_by' => auth()->id(),
                        'sort_order' => $maxTaskOrder,
                    ]);

                    foreach ($taskData['items'] ?? [] as $iIndex => $itemData) {
                        if (empty($itemData['title'])) continue;

                        $itemAssignee = !empty($itemData['assigned_to']) ? (int)$itemData['assigned_to'] : $managerId;
                        $rawItemDueDate = !empty($itemData['due_date']) ? $itemData['due_date'] : $rawDueDate;
                        $itemDueDate = $rawItemDueDate ? $resolveDueDate($rawItemDueDate) : $taskDueDate;

                        if ($itemAssignee && !$project->members()->where('user_id', $itemAssignee)->exists()) {
                            $project->members()->create(['user_id' => $itemAssignee, 'role' => 'editor']);
                        }

                        $task->checklistItems()->create([
                            'title' => trim($itemData['title']),
                            'description' => $itemData['description'] ?? null,
                            'status_id' => !empty($itemData['status_id']) ? $itemData['status_id'] : null,
                            'assigned_to' => $itemAssignee,
                            'due_date' => $itemDueDate,
                            'created_by' => auth()->id(),
                            'is_done' => false,
                            'sort_order' => $iIndex + 1,
                        ]);
                    }

                    $task->syncStatusFromChecklist();
                }
            }

            // 2. Create Unphased Tasks
            foreach ($unphasedTasks as $taskData) {
                if (empty($taskData['title'])) continue;

                $maxTaskOrder++;
                $managerId = !empty($taskData['manager_id']) ? (int)$taskData['manager_id'] : null;
                $rawDueDate = !empty($taskData['due_date']) ? $taskData['due_date'] : null;
                $taskDueDate = $resolveDueDate($rawDueDate);

                if ($managerId && !$project->members()->where('user_id', $managerId)->exists()) {
                    $project->members()->create(['user_id' => $managerId, 'role' => 'editor']);
                }

                $task = $project->tasks()->create([
                    'phase_id' => null,
                    'group_name' => null,
                    'title' => trim($taskData['title']),
                    'description' => $taskData['description'] ?? null,
                    'status_id' => $defaultTaskStatus,
                    'manager_id' => $managerId,
                    'assigned_to' => $managerId,
                    'due_date' => $taskDueDate,
                    'created_by' => auth()->id(),
                    'sort_order' => $maxTaskOrder,
                ]);

                foreach ($taskData['items'] ?? [] as $iIndex => $itemData) {
                    if (empty($itemData['title'])) continue;

                    $itemAssignee = !empty($itemData['assigned_to']) ? (int)$itemData['assigned_to'] : $managerId;
                    $rawItemDueDate = !empty($itemData['due_date']) ? $itemData['due_date'] : $rawDueDate;
                    $itemDueDate = $rawItemDueDate ? $resolveDueDate($rawItemDueDate) : $taskDueDate;

                    if ($itemAssignee && !$project->members()->where('user_id', $itemAssignee)->exists()) {
                        $project->members()->create(['user_id' => $itemAssignee, 'role' => 'editor']);
                    }

                    $task->checklistItems()->create([
                        'title' => trim($itemData['title']),
                        'description' => $itemData['description'] ?? null,
                        'status_id' => !empty($itemData['status_id']) ? $itemData['status_id'] : null,
                        'assigned_to' => $itemAssignee,
                        'due_date' => $itemDueDate,
                        'created_by' => auth()->id(),
                        'is_done' => false,
                        'sort_order' => $iIndex + 1,
                    ]);
                }

                $task->syncStatusFromChecklist();
            }

            ProjectActivity::log(
                projectId: $project->id,
                action: 'project.updated',
                subject: "اعمال الگوی «{$template->title}» بر روی پروژه",
                userId: auth()->id()
            );

            $project->refreshProgress();
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "الگوی «{$template->title}» با موفقیت بر روی پروژه اعمال شد.",
            ]);
        }

        return redirect()->route('projects.projects.show', ['project' => $project->id, 'tab' => 'tasks'])
            ->with('success', "الگوی «{$template->title}» با موفقیت بر روی پروژه اعمال شد.");
    }

    public function export(ProjectTemplate $template)
    {
        abort_unless(auth()->user()?->can('projects.templates.manage'), 403);

        $exportData = [
            'version' => '1.0',
            'title' => $template->title,
            'description' => $template->description,
            'category' => $template->category?->name,
            'exported_at' => now()->toIso8601String(),
            'structure' => $this->normalizeStructure($template->structure),
        ];

        $safeTitle = Str::slug($template->title ?: 'template', '-');
        if (empty($safeTitle)) {
            $safeTitle = 'template-' . $template->id;
        }
        $filename = "template-{$safeTitle}.json";

        return response()->json($exportData, 200, [
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Type' => 'application/json; charset=utf-8',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('projects.templates.manage'), 403);

        $request->validate([
            'template_file' => 'required|file|max:10240',
            'category_id' => 'nullable|exists:projects_categories,id',
            'custom_title' => 'nullable|string|max:255',
        ], [
            'template_file.required' => 'لطفاً فایل JSON الگو را انتخاب کنید.',
            'template_file.file' => 'فایل ارسالی نامعتبر است.',
            'template_file.max' => 'حجم فایل نمی‌تواند بیشتر از ۱۰ مگابایت باشد.',
            'category_id.exists' => 'دسته‌بندی انتخاب‌شده نامعتبر است.',
            'custom_title.max' => 'عنوان نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
        ]);

        $file = $request->file('template_file');
        $content = file_get_contents($file->getRealPath());

        // Remove possible BOM
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return back()->withErrors(['template_file' => 'فایل انتخاب‌شده یک فایل JSON معتبر نیست یا ساختار آن آسیب دیده است. (' . json_last_error_msg() . ')']);
        }

        $customTitle = trim($request->input('custom_title') ?? '');
        $fallbackTitle = $customTitle ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $parsed = $this->extractTemplateDataFromJson($decoded, $fallbackTitle, $request->input('category_id'), $customTitle ?: null);

        $structure = $this->normalizeStructure($parsed['structure']);

        $template = ProjectTemplate::create([
            'title' => $parsed['title'],
            'description' => $parsed['description'],
            'category_id' => $parsed['category_id'],
            'structure' => $structure,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('projects.templates.edit', $template)
            ->with('success', "الگوی «{$template->title}» با موفقیت از فایل JSON شناسایی و ثبت شد. اکنون می‌توانید جزئیات آن را بررسی و ویرایش نمایید.");
    }

    private function extractTemplateDataFromJson(array $data, string $fallbackTitle = 'الگوی جدید', mixed $fallbackCategoryId = null, ?string $overrideTitle = null): array
    {
        // 1. Title: Priority: overrideTitle > data[title] > fallbackTitle
        $title = $overrideTitle ?: (trim($data['title'] ?? $data['name'] ?? $data['عنوان'] ?? $data['نام'] ?? '') ?: $fallbackTitle);

        // 2. Description
        $description = trim($data['description'] ?? $data['desc'] ?? $data['توضیحات'] ?? '') ?: null;

        // 3. Category: Priority: fallbackCategoryId > data[category_id] > data[category]
        $categoryId = $fallbackCategoryId ? (int)$fallbackCategoryId : null;
        if (!$categoryId) {
            if (!empty($data['category_id']) && is_numeric($data['category_id'])) {
                $categoryId = ProjectCategory::where('id', $data['category_id'])->value('id');
            } elseif (!empty($data['category']) && is_string($data['category'])) {
                $categoryId = ProjectCategory::where('name', trim($data['category']))->value('id');
            }
        }

        // 4. Structure
        $structure = ['phases' => [], 'unphased_tasks' => []];

        if (isset($data['structure']) && is_array($data['structure'])) {
            $rawStructure = $data['structure'];
            $structure['phases'] = $rawStructure['phases'] ?? $rawStructure['فازها'] ?? [];
            $structure['unphased_tasks'] = $rawStructure['unphased_tasks'] ?? $rawStructure['گروه‌های عمومی'] ?? $rawStructure['tasks'] ?? [];
        } elseif (isset($data['phases']) || isset($data['فازها'])) {
            $structure['phases'] = $data['phases'] ?? $data['فازها'] ?? [];
            $structure['unphased_tasks'] = $data['unphased_tasks'] ?? $data['tasks'] ?? $data['گروه‌ها'] ?? [];
        } elseif (array_is_list($data)) {
            // Might be a direct list of phases or tasks
            $hasPhases = false;
            foreach ($data as $item) {
                if (is_array($item) && (isset($item['tasks']) || isset($item['فاز']) || isset($item['phases']))) {
                    $hasPhases = true;
                    break;
                }
            }
            if ($hasPhases) {
                $structure['phases'] = $data;
            } else {
                $structure['unphased_tasks'] = $data;
            }
        } elseif (isset($data['tasks']) || isset($data['گروه‌ها'])) {
            $structure['unphased_tasks'] = $data['tasks'] ?? $data['گروه‌ها'] ?? [];
        }

        return [
            'title' => $title,
            'description' => $description,
            'category_id' => $categoryId,
            'structure' => $structure,
        ];
    }
}
