<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                        $taskDueDate = $task->due_date_jalali ?: ($task->due_date ? $task->due_date->format('Y/m/d') : '');

                        $itemsList = [];
                        foreach ($task->checklistItems as $item) {
                            $itemDueDate = $item->due_date_jalali ?: ($item->due_date ? $item->due_date->format('Y/m/d') : $taskDueDate);
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
                    $taskDueDate = $task->due_date_jalali ?: ($task->due_date ? $task->due_date->format('Y/m/d') : '');

                    $itemsList = [];
                    foreach ($task->checklistItems as $item) {
                        $itemDueDate = $item->due_date_jalali ?: ($item->due_date ? $item->due_date->format('Y/m/d') : $taskDueDate);
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

        $normalizedPhases = [];
        foreach ($structure['phases'] ?? [] as $phase) {
            $phaseTasks = [];
            foreach ($phase['tasks'] ?? [] as $task) {
                if (empty($task['title']) && empty($task['items'])) {
                    continue;
                }

                $srcTask = $sourceTasksMap->get($task['title'] ?? '');

                $taskManagerId = ($task['manager_id'] ?? $task['assigned_to'] ?? '') 
                    ?: ($srcTask ? (($srcTask->manager_id ?? $srcTask->assigned_to) ? (string)($srcTask->manager_id ?? $srcTask->assigned_to) : '') : '');
                $taskDueDate = (string)($task['due_date'] ?? ($srcTask ? ($srcTask->due_date_jalali ?: ($srcTask->due_date ? $srcTask->due_date->format('Y/m/d') : '')) : ''));

                $srcItemsMap = $srcTask ? $srcTask->checklistItems->keyBy('title') : collect();

                $items = [];
                foreach ($task['items'] ?? [] as $item) {
                    if (empty($item['title'])) {
                        continue;
                    }

                    $srcItem = $srcItemsMap->get($item['title'] ?? '');

                    $itemAssignee = ($item['assigned_to'] ?? '') 
                        ?: ($srcItem ? ($srcItem->assigned_to ? (string)$srcItem->assigned_to : $taskManagerId) : $taskManagerId);
                    $itemDueDate = (string)($item['due_date'] ?? ($srcItem ? ($srcItem->due_date_jalali ?: ($srcItem->due_date ? $srcItem->due_date->format('Y/m/d') : $taskDueDate)) : $taskDueDate));

                    $items[] = [
                        'title' => trim($item['title'] ?? ''),
                        'description' => trim($item['description'] ?? ''),
                        'assigned_to' => (string)$itemAssignee,
                        'due_date' => (string)$itemDueDate,
                    ];
                }

                $phaseTasks[] = [
                    'title' => trim($task['title'] ?? ''),
                    'description' => trim($task['description'] ?? ''),
                    'manager_id' => (string)$taskManagerId,
                    'due_date' => (string)$taskDueDate,
                    'items' => $items,
                ];
            }

            if (!empty($phase['name']) || !empty($phaseTasks)) {
                $normalizedPhases[] = [
                    'name' => trim($phase['name'] ?? ''),
                    'color' => $phase['color'] ?? '#6366f1',
                    'description' => trim($phase['description'] ?? ''),
                    'tasks' => $phaseTasks,
                ];
            }
        }

        $normalizedUnphased = [];
        foreach ($structure['unphased_tasks'] ?? [] as $task) {
            if (empty($task['title']) && empty($task['items'])) {
                continue;
            }

            $srcTask = $sourceTasksMap->get($task['title'] ?? '');

            $taskManagerId = ($task['manager_id'] ?? $task['assigned_to'] ?? '') 
                ?: ($srcTask ? (($srcTask->manager_id ?? $srcTask->assigned_to) ? (string)($srcTask->manager_id ?? $srcTask->assigned_to) : '') : '');
            $taskDueDate = (string)($task['due_date'] ?? ($srcTask ? ($srcTask->due_date_jalali ?: ($srcTask->due_date ? $srcTask->due_date->format('Y/m/d') : '')) : ''));

            $srcItemsMap = $srcTask ? $srcTask->checklistItems->keyBy('title') : collect();

            $items = [];
            foreach ($task['items'] ?? [] as $item) {
                if (empty($item['title'])) {
                    continue;
                }

                $srcItem = $srcItemsMap->get($item['title'] ?? '');

                $itemAssignee = ($item['assigned_to'] ?? '') 
                    ?: ($srcItem ? ($srcItem->assigned_to ? (string)$srcItem->assigned_to : $taskManagerId) : $taskManagerId);
                $itemDueDate = (string)($item['due_date'] ?? ($srcItem ? ($srcItem->due_date_jalali ?: ($srcItem->due_date ? $srcItem->due_date->format('Y/m/d') : $taskDueDate)) : $taskDueDate));

                $items[] = [
                    'title' => trim($item['title'] ?? ''),
                    'description' => trim($item['description'] ?? ''),
                    'assigned_to' => (string)$itemAssignee,
                    'due_date' => (string)$itemDueDate,
                ];
            }

            $normalizedUnphased[] = [
                'title' => trim($task['title'] ?? ''),
                'description' => trim($task['description'] ?? ''),
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

    public function apply(Request $request, ProjectTemplate $template, Project $project)
    {
        $this->authorize('update', $project);

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
                    $taskDueDate = $rawDueDate ? $this->convertJalaliDate((string)$rawDueDate) : null;

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
                        $itemDueDate = $rawItemDueDate ? $this->convertJalaliDate((string)$rawItemDueDate) : $taskDueDate;

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
                $taskDueDate = $rawDueDate ? $this->convertJalaliDate((string)$rawDueDate) : null;

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
                    $itemDueDate = $rawItemDueDate ? $this->convertJalaliDate((string)$rawItemDueDate) : $taskDueDate;

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
}
