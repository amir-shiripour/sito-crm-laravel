<?php

namespace Modules\Workflows\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Workflows\Entities\Workflow;
use Modules\Workflows\Entities\WorkflowAction;
use Modules\Workflows\Entities\WorkflowStage;
use Modules\Workflows\Entities\WorkflowTrigger;

class WorkflowController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('workflows.view');

        $q = Workflow::query()->withCount('stages');

        // Search
        if ($search = $request->get('q')) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $q->where('is_active', true);
            } elseif ($status === 'inactive') {
                $q->where('is_active', false);
            }
        }

        // Filter by Trigger Type
        if ($request->filled('trigger_type')) {
            $triggerType = $request->get('trigger_type');
            $q->whereHas('triggers', function ($query) use ($triggerType) {
                $query->where('type', $triggerType);
            });
        }

        $workflows = $q->orderBy('created_at', 'desc')->paginate(20);

        // Stats
        $stats = [
            'total' => Workflow::count(),
            'active' => Workflow::where('is_active', true)->count(),
            'inactive' => Workflow::where('is_active', false)->count(),
            // 'executed' => \Modules\Workflows\Entities\WorkflowInstance::count(), // If needed
        ];

        return view('workflows::user.workflows.index', compact('workflows', 'stats'));
    }

    protected function getTriggerOptions(): array
    {
        $triggerOptions = [];

        $svc = \Modules\Booking\Services\AppointmentService::class;

        if (class_exists($svc) && method_exists($svc, 'workflowTriggerOptions')) {
            $triggerOptions['APPOINTMENT'] = $svc::workflowTriggerOptions();
        }

        return $triggerOptions;
    }


    public function create()
    {
        Gate::authorize('workflows.create');

        $triggerOptions = $this->getTriggerOptions();
        $users = User::query()->select(['id', 'name'])->orderBy('name')->get();
        $services = \Modules\Booking\Entities\BookingService::query()->where('status', 'ACTIVE')->get();
        $tokens = config('workflows.tokens', []);

        $cureStatuses = \Modules\Booking\Entities\BookingSetting::current()?->cure_statuses ?? [];
        $cureAssignableRoles = \Modules\Booking\Entities\BookingSetting::current()?->cure_assignable_roles ?? [];
        $cureRoles = \Spatie\Permission\Models\Role::whereIn('id', $cureAssignableRoles)->orderBy('name')->get();

        return view('workflows::user.workflows.create', compact('triggerOptions', 'services', 'users', 'tokens', 'cureStatuses', 'cureAssignableRoles', 'cureRoles'));
    }

    public function store(Request $request)
    {
        Gate::authorize('workflows.create');

        // اگر از dropdown انتخاب شده باشد، key را از همان بگیر
        if ($request->filled('key_preset') && $request->key_preset !== '__custom__') {
            $request->merge(['key' => $request->key_preset]);
        }

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'key'         => ['required', 'string', 'max:255', 'unique:workflows,key'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
            'triggers'    => ['nullable', 'array'],
        ]);

        $workflow = Workflow::query()->create([
            'name'        => $data['name'],
            'key'         => $data['key'],
            'description' => $data['description'] ?? null,
            'is_active'   => (bool) ($data['is_active'] ?? false),
            'created_by'  => $request->user()?->id,
        ]);

        if (!empty($data['triggers'])) {
            $this->syncTriggers($workflow, $data['triggers']);
        }

        return redirect()->route('user.workflows.edit', $workflow)->with('success', 'گردش کار ایجاد شد.');
    }

    public function show(Workflow $workflow)
    {
        Gate::authorize('workflows.view');

        $workflow->load(['stages.actions', 'triggers']);

        return view('workflows::user.workflows.show', compact('workflow'));
    }

    public function edit(Workflow $workflow)
    {
        Gate::authorize('workflows.edit');

        $workflow->load(['stages.actions', 'triggers']);
        $triggerOptions = $this->getTriggerOptions();
        $users = User::query()->select(['id', 'name'])->orderBy('name')->get();
        $services = \Modules\Booking\Entities\BookingService::query()->where('status', 'ACTIVE')->get();

        // Pass tokens to view
        $tokens = config('workflows.tokens', []);

        $cureStatuses = \Modules\Booking\Entities\BookingSetting::current()?->cure_statuses ?? [];
        $cureAssignableRoles = \Modules\Booking\Entities\BookingSetting::current()?->cure_assignable_roles ?? [];
        $cureRoles = \Spatie\Permission\Models\Role::whereIn('id', $cureAssignableRoles)->orderBy('name')->get();

        return view('workflows::user.workflows.edit', compact('workflow', 'triggerOptions', 'users', 'services', 'tokens', 'cureStatuses', 'cureAssignableRoles', 'cureRoles'));
    }

    public function update(Request $request, Workflow $workflow)
    {
        Gate::authorize('workflows.edit');

        // اگر از dropdown انتخاب شده باشد، key را از همان بگیر
        if ($request->filled('key_preset') && $request->key_preset !== '__custom__') {
            $request->merge(['key' => $request->key_preset]);
        }

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'key'         => ['required', 'string', 'max:255', 'unique:workflows,key,' . $workflow->id],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
            'triggers'    => ['nullable', 'array'],
        ]);

        $workflow->update([
            'name'        => $data['name'],
            'key'         => $data['key'],
            'description' => $data['description'] ?? null,
            'is_active'   => (bool) ($data['is_active'] ?? false),
        ]);

        if (isset($data['triggers'])) {
            $this->syncTriggers($workflow, $data['triggers']);
        }

        return back()->with('success', 'گردش کار به‌روزرسانی شد.');
    }

    protected function syncTriggers(Workflow $workflow, array $triggersData): void
    {
        // For now, we replace all triggers.
        // In a more complex UI, we might update existing ones.
        $workflow->triggers()->delete();

        foreach ($triggersData as $tData) {
            if (empty($tData['type'])) continue;

            $config = $tData['config'] ?? [];

            // Clean up config arrays to remove null/empty elements
            foreach ($config as $cKey => $cVal) {
                if (is_array($cVal)) {
                    $config[$cKey] = array_values(array_filter($cVal, function ($v) {
                        return $v !== null && $v !== '';
                    }));
                }
            }

            $workflow->triggers()->create([
                'type'   => $tData['type'],
                'config' => $config,
            ]);
        }
    }

    public function destroy(Workflow $workflow)
    {
        Gate::authorize('workflows.delete');
        $workflow->delete();

        return redirect()->route('user.workflows.index')->with('success', 'گردش کار حذف شد.');
    }

    public function storeStage(Request $request, Workflow $workflow)
    {
        Gate::authorize('workflows.manage');

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_initial'  => ['boolean'],
            'is_final'    => ['boolean'],
        ]);

        $stage = $workflow->stages()->create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order'  => $data['sort_order'] ?? 0,
            'is_initial'  => (bool) ($data['is_initial'] ?? false),
            'is_final'    => (bool) ($data['is_final'] ?? false),
        ]);

        $this->enforceSingleInitialAndFinal($workflow, $stage);

        return back()->with('success', 'مرحله اضافه شد.');
    }

    public function updateStage(Request $request, Workflow $workflow, WorkflowStage $stage)
    {
        Gate::authorize('workflows.manage');

        if ($stage->workflow_id !== $workflow->id) {
            abort(404);
        }

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_initial'  => ['boolean'],
            'is_final'    => ['boolean'],
        ]);

        $stage->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order'  => $data['sort_order'] ?? 0,
            'is_initial'  => (bool) ($data['is_initial'] ?? false),
            'is_final'    => (bool) ($data['is_final'] ?? false),
        ]);

        $this->enforceSingleInitialAndFinal($workflow, $stage);

        return back()->with('success', 'مرحله به‌روزرسانی شد.');
    }

    public function destroyStage(Workflow $workflow, WorkflowStage $stage)
    {
        Gate::authorize('workflows.manage');
        if ($stage->workflow_id !== $workflow->id) {
            abort(404);
        }

        $stage->delete();

        return back()->with('success', 'مرحله حذف شد.');
    }

    public function storeAction(Request $request, Workflow $workflow, WorkflowStage $stage)
    {
        Gate::authorize('workflows.manage');
        if ($stage->workflow_id !== $workflow->id) {
            abort(404);
        }

        $action = $stage->actions()->create($this->validatedActionPayload($request));

        return back()->with('success', 'اکشن اضافه شد.');
    }

    public function updateAction(Request $request, Workflow $workflow, WorkflowStage $stage, WorkflowAction $action)
    {
        Gate::authorize('workflows.manage');
        if ($stage->workflow_id !== $workflow->id || $action->stage_id !== $stage->id) {
            abort(404);
        }

        $action->update($this->validatedActionPayload($request));

        return back()->with('success', 'اکشن به‌روزرسانی شد.');
    }

    public function destroyAction(Workflow $workflow, WorkflowStage $stage, WorkflowAction $action)
    {
        Gate::authorize('workflows.manage');
        if ($stage->workflow_id !== $workflow->id || $action->stage_id !== $stage->id) {
            abort(404);
        }

        $action->delete();

        return back()->with('success', 'اکشن حذف شد.');
    }

    protected function enforceSingleInitialAndFinal(Workflow $workflow, WorkflowStage $stage): void
    {
        if ($stage->is_initial) {
            $workflow->stages()
                ->where('id', '<>', $stage->id)
                ->where('is_initial', true)
                ->update(['is_initial' => false]);
        }

        if ($stage->is_final) {
            $workflow->stages()
                ->where('id', '<>', $stage->id)
                ->where('is_final', true)
                ->update(['is_final' => false]);
        }
    }

    protected function validatedActionPayload(Request $request): array
    {
        $data = $request->validate([
            'action_type' => ['required', 'string', 'in:' . implode(',', [
                    WorkflowAction::TYPE_CREATE_TASK,
                    WorkflowAction::TYPE_CREATE_FOLLOWUP,
                    WorkflowAction::TYPE_SEND_NOTIFICATION,
                    WorkflowAction::TYPE_SEND_SMS,
                ])],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'config'      => ['nullable', 'array'],
        ]);

        $config = $data['config'] ?? [];

        if ($data['action_type'] === WorkflowAction::TYPE_SEND_SMS) {
            $config = [
                'target'         => $config['target'] ?? 'APPOINTMENT_CLIENT',
                'target_user_id' => $config['target_user_id'] ?? null,
                'phone'          => $config['phone'] ?? null,
                'pattern_key'    => $config['pattern_key'] ?? null,
                'message'        => $config['message'] ?? null,
                'params'         => array_values(array_filter($config['params'] ?? [], fn($v) => $v !== null && $v !== '')),
                'offset_minutes' => isset($config['offset_minutes']) ? (int) $config['offset_minutes'] : null,
            ];
        } elseif (in_array($data['action_type'], [WorkflowAction::TYPE_CREATE_TASK, WorkflowAction::TYPE_CREATE_FOLLOWUP])) {
             $config = [
                'title'           => $config['title'] ?? null,
                'description'     => $config['description'] ?? null,
                'assignee_target' => $config['assignee_target'] ?? 'CURRENT_USER',
                'assignee_id'     => $config['assignee_id'] ?? null,
                'offset_days'     => isset($config['offset_days']) ? (int) $config['offset_days'] : 0,
                'priority'        => $config['priority'] ?? 'MEDIUM',
                'status'          => $config['status'] ?? 'TODO',
            ];
        }

        return [
            'action_type' => $data['action_type'],
            'sort_order'  => $data['sort_order'] ?? 0,
            'config'      => $config,
        ];
    }

    public function designer(Workflow $workflow)
    {
        Gate::authorize('workflows.view');

        $workflow->load(['nodes', 'edges']);

        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();
        $users = \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get();
        
        $subWorkflows = Workflow::where('id', '!=', $workflow->id)
            ->where('is_active', true)
            ->get();

        $cureStatuses = \Modules\Booking\Entities\BookingSetting::current()?->cure_statuses ?? [];
        $cureAssignableRoles = \Modules\Booking\Entities\BookingSetting::current()?->cure_assignable_roles ?? [];
        $cureRoles = \Spatie\Permission\Models\Role::whereIn('id', $cureAssignableRoles)->orderBy('name')->get();

        return view('workflows::user.workflows.designer', compact('workflow', 'roles', 'subWorkflows', 'users', 'cureStatuses', 'cureAssignableRoles', 'cureRoles'));
    }

    public function saveGraph(Request $request, Workflow $workflow)
    {
        Gate::authorize('workflows.edit');

        $data = $request->validate([
            'nodes'   => ['required', 'array'],
            'nodes.*.id' => ['required'],
            'nodes.*.name' => ['required', 'string'],
            'nodes.*.type' => ['required', 'string'],
            'nodes.*.config' => ['nullable', 'array'],
            'nodes.*.x' => ['nullable', 'numeric'],
            'nodes.*.y' => ['nullable', 'numeric'],
            'edges'   => ['nullable', 'array'],
            'edges.*.source_id' => ['required'],
            'edges.*.target_id' => ['required'],
            'edges.*.condition' => ['nullable', 'string'],
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($workflow, $data) {
            $existingNodes = $workflow->nodes()->get();
            $incomingNodes = $data['nodes'];

            $incomingIds = [];
            foreach ($incomingNodes as $nodeData) {
                if (is_numeric($nodeData['id'])) {
                    $incomingIds[] = (int) $nodeData['id'];
                }
            }

            $nodesToDelete = $existingNodes->filter(function ($node) use ($incomingIds) {
                return !in_array($node->id, $incomingIds);
            });

            if ($nodesToDelete->isNotEmpty()) {
                $nodeIdsToDelete = $nodesToDelete->pluck('id')->toArray();
                
                $activeInstancesCount = \Modules\Workflows\Entities\WorkflowInstance::query()
                    ->where('workflow_id', $workflow->id)
                    ->where('status', \Modules\Workflows\Entities\WorkflowInstance::STATUS_ACTIVE)
                    ->whereIn('current_node_id', $nodeIdsToDelete)
                    ->count();

                if ($activeInstancesCount > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'برخی از گره‌ها به فرآیندهای فعال متصل هستند و حذف آنها باعث اختلال در روند درمان بیماران می‌شود.'
                    ], 422);
                }
            }

            $workflow->edges()->delete();

            if ($nodesToDelete->isNotEmpty()) {
                $workflow->nodes()->whereIn('id', $nodesToDelete->pluck('id'))->delete();
            }

            $idMap = [];

            foreach ($incomingNodes as $nodeData) {
                $config = $nodeData['config'] ?? [];
                $config['x'] = $nodeData['x'] ?? 0;
                $config['y'] = $nodeData['y'] ?? 0;

                if (is_numeric($nodeData['id']) && $existingNodes->contains('id', $nodeData['id'])) {
                    $node = $workflow->nodes()->find($nodeData['id']);
                    $node->update([
                        'name'   => $nodeData['name'],
                        'type'   => $nodeData['type'],
                        'config' => $config,
                    ]);
                    $idMap[$nodeData['id']] = $node->id;
                } else {
                    $node = $workflow->nodes()->create([
                        'name'   => $nodeData['name'],
                        'type'   => $nodeData['type'],
                        'config' => $config,
                    ]);
                    $idMap[$nodeData['id']] = $node->id;
                }
            }

            if (!empty($data['edges'])) {
                foreach ($data['edges'] as $edgeData) {
                    $sourceDbId = $idMap[$edgeData['source_id']] ?? null;
                    $targetDbId = $idMap[$edgeData['target_id']] ?? null;

                    if ($sourceDbId && $targetDbId) {
                        $workflow->edges()->create([
                            'source_node_id' => $sourceDbId,
                            'target_node_id' => $targetDbId,
                            'condition'      => $edgeData['condition'] ?? null,
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'طرح گرافیکی گردش‌کار با موفقیت ذخیره شد.'
            ]);
        });
    }

    public function advanceInstance(Request $request, \Modules\Workflows\Entities\WorkflowInstance $instance, \Modules\Workflows\Services\WorkflowEngine $engine)
    {
        Gate::authorize('workflows.edit');
        if ($instance->status !== \Modules\Workflows\Entities\WorkflowInstance::STATUS_ACTIVE) {
            return response()->json(['success' => false, 'message' => 'فرآیند غیرفعال است.'], 422);
        }
        
        $context = $engine->buildContextData($instance, $request->all());
        $engine->advance($instance, $context);
        
        return response()->json([
            'success' => true,
            'message' => 'فرآیند با موفقیت به گام بعدی هدایت شد.'
        ]);
    }

    public function goBackInstance(Request $request, \Modules\Workflows\Entities\WorkflowInstance $instance, \Modules\Workflows\Services\WorkflowEngine $engine)
    {
        Gate::authorize('workflows.edit');
        if ($instance->status !== \Modules\Workflows\Entities\WorkflowInstance::STATUS_ACTIVE) {
            return response()->json(['success' => false, 'message' => 'فرآیند غیرفعال است.'], 422);
        }

        $engine->goBack($instance);

        return response()->json([
            'success' => true,
            'message' => 'فرآیند یک مرحله به عقب بازگشت.'
        ]);
    }

    public function cancelInstance(\Modules\Workflows\Entities\WorkflowInstance $instance)
    {
        Gate::authorize('workflows.edit');
        $instance->update([
            'status' => \Modules\Workflows\Entities\WorkflowInstance::STATUS_CANCELED,
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'فرآیند لغو شد.'
        ]);
    }

    public function restartInstance(\Modules\Workflows\Entities\WorkflowInstance $instance, \Modules\Workflows\Services\WorkflowEngine $engine)
    {
        Gate::authorize('workflows.edit');
        
        // Cancel the current active one
        $instance->update([
            'status' => \Modules\Workflows\Entities\WorkflowInstance::STATUS_CANCELED,
            'completed_at' => now(),
        ]);

        // Start a new one
        $new = $engine->startNodeWorkflow(
            $instance->workflow,
            $instance->related_type,
            $instance->related_id
        );

        return response()->json([
            'success' => true,
            'message' => 'فرآیند مجدداً راه‌اندازی شد.',
            'instance' => $new
        ]);
    }
}

