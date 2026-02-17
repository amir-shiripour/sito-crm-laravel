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
        Gate::authorize('workflows.manage');

        $triggerOptions = $this->getTriggerOptions();
        $services = \Modules\Booking\Entities\BookingService::query()->where('status', 'ACTIVE')->get();

        return view('workflows::user.workflows.create', compact('triggerOptions', 'services'));
    }

    public function store(Request $request)
    {
        Gate::authorize('workflows.manage');

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
        Gate::authorize('workflows.manage');

        $workflow->load(['stages.actions', 'triggers']);
        $triggerOptions = $this->getTriggerOptions();
        $users = User::query()->select(['id', 'name'])->orderBy('name')->get();
        $services = \Modules\Booking\Entities\BookingService::query()->where('status', 'ACTIVE')->get();

        // Pass tokens to view
        $tokens = config('workflows.tokens', []);

        return view('workflows::user.workflows.edit', compact('workflow', 'triggerOptions', 'users', 'services', 'tokens'));
    }

    public function update(Request $request, Workflow $workflow)
    {
        Gate::authorize('workflows.manage');

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

            $workflow->triggers()->create([
                'type'   => $tData['type'],
                'config' => $tData['config'] ?? [],
            ]);
        }
    }

    public function destroy(Workflow $workflow)
    {
        Gate::authorize('workflows.manage');
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
}
