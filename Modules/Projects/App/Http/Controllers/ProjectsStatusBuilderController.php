<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Modules\Projects\App\Http\Models\ProjectStatus;
use Modules\Projects\App\Services\ProjectStatusBuilderService;
use Spatie\Permission\Models\Role;

class ProjectsStatusBuilderController extends Controller
{
    public function __construct(private ProjectStatusBuilderService $svc)
    {
    }

    public function index()
    {
        $this->authorize('projects.status-builder.manage');

        $roles = Role::all();
        $users = User::all();
        $data = array_merge($this->svc->allGrouped(), [
            'roles' => $roles,
            'users' => $users,
        ]);

        return view('projects::status-builder.index', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('projects.status-builder.manage');

        $validated = $request->validate($this->validationRules());
        $status = $this->svc->create($this->prepareData($validated));

        return back()->with('success', "وضعیت «{$status->name}» با موفقیت اضافه شد.");
    }

    public function update(Request $request, ProjectStatus $status)
    {
        $this->authorize('projects.status-builder.manage');

        $validated = $request->validate($this->validationRules());
        $this->svc->update($status, $this->prepareData($validated));

        return back()->with('success', 'وضعیت با موفقیت ویرایش شد.');
    }

    public function destroy(ProjectStatus $status)
    {
        $this->authorize('projects.status-builder.manage');

        $isSuperAdmin = auth()->user()->hasAnyRole(['super-admin', 'superadmin']);
        if (!$isSuperAdmin && ($status->is_default || $status->is_readonly)) {
            return back()->with('error', 'امکان حذف وضعیت‌های پیش‌فرض یا فقط‌خواندنی سیستم وجود ندارد.');
        }

        $status->delete();

        return back()->with('success', 'وضعیت با موفقیت حذف شد.');
    }

    public function reorder(Request $request)
    {
        $this->authorize('projects.status-builder.manage');

        $request->validate(['ids' => 'required|array']);
        $this->svc->reorder($request->ids);

        return response()->json(['ok' => true]);
    }

    public function seed()
    {
        $this->authorize('projects.status-builder.manage');

        Artisan::call('db:seed', [
            '--class' => 'Modules\Projects\Database\Seeders\ProjectStatusSeeder',
            '--force' => true,
        ]);

        return back()->with('success', 'وضعیت‌های پیش‌فرض با موفقیت ساخته و بازنشانی شدند.');
    }

    private function prepareData(array $validated): array
    {
        $attributeKeys = [
            'is_in_progress',
            'is_queued',
            'is_completed',
            'is_canceled',
            'is_delayed',
        ];

        $attributes = [];
        foreach ($attributeKeys as $key) {
            if (array_key_exists($key, $validated)) {
                $attributes[$key] = filter_var($validated[$key], FILTER_VALIDATE_BOOLEAN);
                unset($validated[$key]);
            } else {
                $attributes[$key] = false;
            }
        }
        $validated['attributes'] = $attributes;

        $validated['is_final'] = filter_var($validated['is_final'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $validated['is_default'] = filter_var($validated['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $validated['is_readonly'] = filter_var($validated['is_readonly'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $validated['allowed_roles']       = !empty($validated['allowed_roles']) ? array_values($validated['allowed_roles']) : null;
        $validated['allowed_users']       = !empty($validated['allowed_users']) ? array_values(array_map('intval', $validated['allowed_users'])) : null;
        $validated['allowed_transitions'] = !empty($validated['allowed_transitions']) ? array_values(array_map('intval', $validated['allowed_transitions'])) : null;

        return $validated;
    }

    private function validationRules(): array
    {
        return [
            'name'                => 'required|string|max:100',
            'color'               => 'required|string|max:20',
            'icon'                => 'nullable|string|max:50',
            'type'                => 'required|in:project,task,checklist',
            'is_final'            => 'nullable|boolean',
            'is_default'          => 'nullable|boolean',
            'is_readonly'         => 'nullable|boolean',
            'allowed_roles'       => 'nullable|array',
            'allowed_users'       => 'nullable|array',
            'allowed_transitions' => 'nullable|array',
            'allowed_transitions.*' => 'integer|exists:projects_statuses,id',
            'is_in_progress'      => 'nullable|boolean',
            'is_queued'           => 'nullable|boolean',
            'is_completed'        => 'nullable|boolean',
            'is_canceled'         => 'nullable|boolean',
            'is_delayed'          => 'nullable|boolean',
        ];
    }
}
