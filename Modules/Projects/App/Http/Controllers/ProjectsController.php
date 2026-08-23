<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Clients\Entities\Client;
use Modules\Projects\App\Exceptions\InvalidStatusTransitionException;
use Modules\Projects\App\Http\Models\Project;
use Modules\Projects\App\Http\Models\ProjectCategory;
use Modules\Projects\App\Http\Models\ProjectRole;
use Modules\Projects\App\Http\Models\ProjectSetting;
use Modules\Projects\App\Http\Models\ProjectStatus;
use Modules\Projects\App\Http\Requests\StoreProjectRequest;
use Modules\Projects\App\Services\ProjectsService;
use Modules\Projects\App\Services\StatusTransitionService;

class ProjectsController extends Controller
{
    public function __construct(
        private ProjectsService $svc,
        private StatusTransitionService $transitionSvc,
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $userId = auth()->id();
        $isSuperAdmin = auth()->user()->hasAnyRole(['super-admin', 'superadmin']);

        $baseQuery = Project::with(['category', 'status', 'client', 'createdBy', 'members.user'])
            ->projectsOnly()
            ->withCount(['tasks', 'phases'])
            ->when(!$isSuperAdmin && !auth()->user()->can('projects.manage'), function ($q) use ($userId) {
                $q->where(function ($sub) use ($userId) {
                    $sub->where('created_by', $userId)
                        ->orWhereHas('members', fn($m) => $m->where('user_id', $userId));
                });
            });

        $filterQuery = (clone $baseQuery)
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('status_id'), fn($q) => $q->where('status_id', $request->status_id))
            ->when($request->filled('client_id'), fn($q) => $q->where('client_id', $request->client_id));

        $perPage = (int)$request->get('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $projects = (clone $filterQuery)->latest()->paginate($perPage, ['*'], 'page')->withQueryString();

        // Stats for cards
        $allUserProjects = (clone $filterQuery)->get();
        $stats = [
            'total' => $allUserProjects->count(),
            'queued' => $allUserProjects->filter(fn($p) => $p->isQueued())->count(),
            'in_progress' => $allUserProjects->filter(fn($p) => $p->isInProgress())->count(),
            'completed' => $allUserProjects->filter(fn($p) => $p->isCompleted())->count(),
            'delayed' => $allUserProjects->filter(fn($p) => $p->isDelayed() || $p->isOverdue())->count(),
            'canceled' => $allUserProjects->filter(fn($p) => $p->isCanceled())->count(),
        ];

        $categories = ProjectCategory::active()->ordered()->get();
        $statuses = ProjectStatus::forType('project')->get();
        $clients = class_exists(Client::class) ? Client::orderBy('full_name')->get() : collect();

        return view('projects::projects.index', compact(
            'projects',
            'categories',
            'statuses',
            'clients',
            'stats'
        ));
    }

    public function create()
    {
        $this->authorize('create', Project::class);

        $categories = ProjectCategory::active()->ordered()->get();
        $statuses = ProjectStatus::forType('project')->get();
        $selectedClientId = old('client_id', request('client_id'));
        $initialClient = null;
        if ($selectedClientId && class_exists(Client::class)) {
            $initialClient = Client::find($selectedClientId);
        }

        $users = User::orderBy('name')->get();

        $nextCode = Project::generateNextCode();
        $codeAuto = ProjectSetting::getBool('projects_code_auto', true);
        $defaultCategoryId = ProjectSetting::get('projects_default_category_id');
        $defaultStatusId = ProjectSetting::get('projects_default_status_id');
        $defaultCreatorRole = ProjectSetting::get('projects_default_creator_role', 'manager');
        $defaultMemberRole = ProjectSetting::get('projects_default_member_role', 'viewer');
        $requireClient = ProjectSetting::getBool('projects_require_client', false);
        $requireDates = ProjectSetting::getBool('projects_require_dates', false);

        $roles = ProjectRole::orderBy('sort_order')->orderBy('id')->get();

        return view('projects::projects.create', compact(
            'categories',
            'statuses',
            'initialClient',
            'users',
            'nextCode',
            'codeAuto',
            'defaultCategoryId',
            'defaultStatusId',
            'defaultCreatorRole',
            'defaultMemberRole',
            'requireClient',
            'requireDates',
            'roles'
        ));
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $validated = $request->validated();
        $project = $this->svc->create($validated, auth()->id());

        return redirect()->route('projects.projects.show', $project)
            ->with('success', "پروژه «{$project->title}» با موفقیت ایجاد شد.");
    }

    public function show(Project $project, Request $request)
    {
        $this->authorize('view', $project);

        $project->load([
            'category',
            'status',
            'client',
            'createdBy',
            'template',
            'members.user',
            'phases',
            'tasks' => fn($q) => $q->orderBy('sort_order')->with(['status', 'phase', 'assignee', 'checklistItems.status', 'checklistItems.assignee', 'checklistItems.assignees', 'checklistItems.comments.user', 'timeLogs.user', 'comments.user']),
            'documents' => fn($q) => $q->latest()->with('uploader'),
            'messages' => fn($q) => $q->oldest()->with(['user', 'pinnedBy', 'parent.user']),
            'activities' => fn($q) => $q->latest('created_at')->limit(500)->with(['user', 'task']),
        ]);

        $tab = $request->get('tab', 'dashboard');

        $statuses = ProjectStatus::forType('project')->get();
        $taskStatuses = ProjectStatus::forType('task')->get();
        $checklistStatuses = ProjectStatus::forType('checklist')->get();
        $users = User::orderBy('name')->get();
        $dashboardStats = $this->svc->dashboardStats($project);

        return view('projects::projects.show', compact(
            'project',
            'tab',
            'statuses',
            'taskStatuses',
            'checklistStatuses',
            'users',
            'dashboardStats'
        ));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        $project->load(['members.user', 'client']);

        $categories = ProjectCategory::active()->ordered()->get();
        $statuses = ProjectStatus::forType('project')->get();
        $selectedClientId = old('client_id', $project->client_id);
        $initialClient = null;
        if ($selectedClientId && class_exists(Client::class)) {
            $initialClient = ($project->client && $project->client->id == $selectedClientId)
                ? $project->client
                : Client::find($selectedClientId);
        }
        $users = User::orderBy('name')->get();
        $roles = ProjectRole::orderBy('sort_order')->orderBy('id')->get();

        return view('projects::projects.edit', compact('project', 'categories', 'statuses', 'initialClient', 'users', 'roles'));
    }

    public function update(StoreProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $this->svc->update($project, $request->validated());

        return redirect()->route('projects.projects.show', $project)
            ->with('success', 'پروژه با موفقیت ویرایش شد.');
    }

    public function cancel(Project $project): RedirectResponse
    {
        $this->authorize('cancel', $project);

        $canceledStatus = ProjectStatus::canceledFor('project');

        if (!$canceledStatus) {
            return back()->with('error', 'وضعیت «لغو شده» در بخش وضعیت‌های پروژه یافت نشد.');
        }

        $project->update(['status_id' => $canceledStatus->id]);

        return back()->with('success', "پروژه «{$project->title}» با موفقیت لغو شد و وضعیت آن به «{$canceledStatus->name}» تغییر یافت.");
    }

    public function destroy(Project $project): RedirectResponse
    {
        return back()->with('error', 'امکان حذف پروژه‌ها وجود ندارد. لطفاً از دکمه لغو پروژه استفاده نمایید.');
    }

    public function updateStatus(Request $request, Project $project)
    {
        $this->authorize('changeStatus', $project);

        $request->validate([
            'status_id' => 'required|exists:projects_statuses,id',
        ]);

        $newStatus = ProjectStatus::findOrFail($request->status_id);
        try {
            $this->transitionSvc->assertCanTransition($project->status, $newStatus);
        } catch (InvalidStatusTransitionException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        $project->update(['status_id' => $newStatus->id]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $project->fresh('status')->status,
            ]);
        }

        return back()->with('success', 'وضعیت پروژه به‌روزرسانی شد.');
    }

    public function dashboardData(Project $project)
    {
        $this->authorize('view', $project);

        return response()->json($this->svc->dashboardStats($project));
    }

    public function searchUsers(Request $request)
    {
        $query = trim($request->get('q', ''));
        $ids = $request->get('ids', '');
        $limit = min((int)$request->get('limit', 20), 50);

        $usersQuery = User::query();

        if ($ids) {
            $idsArray = array_filter(array_map('intval', explode(',', $ids)));
            if (!empty($idsArray)) {
                $usersQuery->whereIn('id', $idsArray);
            }
        } elseif ($query) {
            $usersQuery->where(function ($sub) use ($query) {
                $sub->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('mobile', 'like', "%{$query}%");
            });
        } else {
            return response()->json([
                'results' => [],
                'total' => 0,
            ]);
        }

        $users = $usersQuery
            ->select('id', 'name', 'email', 'mobile', 'profile_photo_path')
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(function ($u) {
                $parts = [$u->name];
                if ($u->email) {
                    $parts[] = $u->email;
                } elseif ($u->mobile) {
                    $parts[] = $u->mobile;
                }

                return [
                    'id' => $u->id,
                    'value' => (string)$u->id,
                    'name' => $u->name,
                    'email' => $u->email ?? '',
                    'mobile' => $u->mobile ?? '',
                    'label' => implode(' | ', $parts),
                    'avatar' => method_exists($u, 'getProfilePhotoUrlAttribute') ? $u->profile_photo_url : null,
                ];
            });

        return response()->json([
            'results' => $users,
            'total' => $users->count(),
        ]);
    }
}
