<?php

namespace Modules\Services\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Services\App\Http\Models\Project;
use Modules\Services\App\Http\Models\Service;
use Modules\Services\App\Http\Models\Status;
use Modules\Services\App\Http\Requests\StoreProjectRequest;
use Modules\Services\App\Services\ProjectService;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $svc)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::with('service', 'customer', 'status', 'assignedUser')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%"))
            ->when($request->service_id, fn($q, $v) => $q->where('service_id', $v))
            ->when($request->status_id, fn($q, $v) => $q->where('status_id', $v))
            ->when($request->customer_id, fn($q, $v) => $q->where('customer_id', $v))
            ->when($request->priority, fn($q, $v) => $q->where('priority', $v))
            ->latest()->paginate(20)->withQueryString();

        $statuses = Status::query()->where('type', 'project')->orderBy('sort_order')->get();
        $services = Service::active()->orderBy('name')->get();
        $customers = User::orderBy('name')->get();

        return view('services::projects.index', compact('projects', 'statuses', 'services', 'customers'));
    }

    public function create()
    {
        $this->authorize('create', Project::class);

        return view('services::projects.create', [
            'project' => null,
            'services' => Service::active()->orderBy('name')->get(),
            'statuses' => Status::query()->where('type', 'project')->orderBy('sort_order')->get(),
            'customers' => User::orderBy('name')->get(),
            'staff' => User::orderBy('name')->get(),
        ]);
    }

    public function store(StoreProjectRequest $request)
    {
        $project = $this->svc->create($request->validated());
        return redirect()->route('services.projects.show', $project)->with('success', 'پروژه ایجاد شد.');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);
        $project->load('service', 'customer', 'assignedUser', 'status', 'invoices.status', 'activities.user');

        $allStatuses = Status::query()->where('type', 'project')->orderBy('sort_order')->get();
        $nextStatuses = $allStatuses->filter(fn($s) => $project->status->canTransitionTo($s) && $s->id !== $project->status_id);

        return view('services::projects.show', compact('project', 'nextStatuses'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        return view('services::projects.edit', [
            'project' => $project,
            'services' => Service::active()->orderBy('name')->get(),
            'statuses' => Status::query()->where('type', 'project')->orderBy('sort_order')->get(),
            'customers' => User::orderBy('name')->get(),
            'staff' => User::orderBy('name')->get(),
        ]);
    }

    public function update(StoreProjectRequest $request, Project $project)
    {
        $this->svc->update($project, $request->validated());
        return redirect()->route('services.projects.show', $project)->with('success', 'پروژه ویرایش شد.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return redirect()->route('services.projects.index')->with('success', 'پروژه حذف شد.');
    }

    public function changeStatus(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        $request->validate(['status_id' => 'required|exists:services_statuses,id']);

        try {
            $this->svc->changeStatus($project, $request->status_id);
            return back()->with('success', 'وضعیت تغییر کرد.');
        } catch (\LogicException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }
}
