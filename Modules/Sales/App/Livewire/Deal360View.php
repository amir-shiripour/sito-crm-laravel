<?php

declare(strict_types=1);

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Modules\Sales\App\Models\SalesDeal;
use Modules\Sales\App\Models\SalesPipeline;
use Modules\Sales\App\Models\SalesCall;
use Modules\ClientCalls\Entities\ClientCall;
use Modules\Tasks\Entities\Task;
use Modules\Clients\Entities\Client;
use Modules\Clients\Entities\ClientForm;

class Deal360View extends Component
{
    public int $dealId;
    public ?SalesDeal $deal = null;
    public array $timeline = [];
    public array $clientCustomFields = [];
    
    // Quick Add Tasks/Followups
    public string $newTaskTitle = '';
    public string $newTaskPriority = 'MEDIUM';
    public ?string $newTaskDueAt = null;
    public string $newTaskDescription = '';

    // Close Deal Properties
    public bool $showCloseModal = false;
    public string $closeType = 'won'; // 'won' or 'lost'
    public float $closingRevenue = 0.0;
    public ?int $closingLossReasonId = null;

    public function mount(int $dealId)
    {
        $this->dealId = $dealId;
        $this->loadDeal();
        $this->loadTimeline();
        $this->loadClientCustomFields();
    }

    public function loadDeal()
    {
        $this->deal = SalesDeal::with(['client', 'stage', 'owner', 'lossReason'])
            ->findOrFail($this->dealId);
    }

    public function loadTimeline()
    {
        if (!$this->deal) return;

        $timelineEvents = [];

        // 1. Load Sales Calls
        $salesCalls = SalesCall::where('deal_id', $this->dealId)
            ->orWhere(function($query) {
                if ($this->deal->client_id) {
                    $query->where('client_id', $this->deal->client_id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->with('user')
            ->latest('call_date')
            ->latest('call_time')
            ->get();

        foreach ($salesCalls as $call) {
            $dateTime = $call->call_date->format('Y-m-d') . ' ' . ($call->call_time ?? '00:00:00');
            $timelineEvents[] = [
                'type' => 'call',
                'title' => 'تماس فروش: ' . ($call->direction === 'inbound' ? 'ورودی' : 'خروجی'),
                'description' => $call->result ?? $call->reason ?? 'جزئیات تماس ثبت نشده است.',
                'date' => $dateTime,
                'user' => $call->user?->name ?? 'سیستم',
                'status' => $call->status,
                'timestamp' => strtotime($dateTime),
            ];
        }

        // 2. Load Client Calls (if module exists)
        if (class_exists(ClientCall::class) && $this->deal->client_id) {
            $clientCalls = ClientCall::where('client_id', $this->deal->client_id)
                ->with('user')
                ->latest('call_date')
                ->latest('call_time')
                ->get();

            foreach ($clientCalls as $call) {
                $dateTime = $call->call_date . ' ' . ($call->call_time ?? '00:00:00');
                $timelineEvents[] = [
                    'type' => 'call',
                    'title' => 'تماس مشتری: ' . ($call->direction === 'inbound' ? 'ورودی' : 'خروجی'),
                    'description' => $call->result ?? $call->reason ?? 'جزئیات تماس ثبت نشده است.',
                    'date' => $dateTime,
                    'user' => $call->user?->name ?? 'سیستم',
                    'status' => $call->status,
                    'timestamp' => strtotime($dateTime),
                ];
            }
        }

        // 3. Load Tasks
        $tasks = Task::where(function($query) {
            $query->where('related_id', $this->dealId)->where('related_type', 'DEAL');
        })->orWhere(function($query) {
            if ($this->deal->client_id) {
                $query->where('related_id', $this->deal->client_id)->where('related_type', 'CLIENT');
            } else {
                $query->whereRaw('1 = 0');
            }
        })
        ->with(['creator', 'assignee'])
        ->get();

        foreach ($tasks as $task) {
            $date = $task->completed_at ? $task->completed_at->toDateTimeString() : ($task->created_at ? $task->created_at->toDateTimeString() : now()->toDateTimeString());
            $timelineEvents[] = [
                'type' => 'task',
                'title' => ($task->task_type === Task::TYPE_FOLLOW_UP ? 'پیگیری فروش' : 'وظیفه') . ': ' . $task->title,
                'description' => $task->description ?? 'توضیحاتی ثبت نشده است.',
                'date' => $date,
                'user' => $task->assignee?->name ?? 'بدون مسئول',
                'status' => $task->status,
                'timestamp' => strtotime($date),
            ];
        }

        // 4. Load Deal stage movement logs (using created_at and stage_entered_at)
        $date = $this->deal->stage_entered_at ? $this->deal->stage_entered_at->toDateTimeString() : $this->deal->created_at->toDateTimeString();
        $timelineEvents[] = [
            'type' => 'stage_change',
            'title' => 'تغییر مرحله خط لوله',
            'description' => 'پرونده وارد مرحله «' . ($this->deal->stage?->name ?? 'نامشخص') . '» شد.',
            'date' => $date,
            'user' => $this->deal->owner?->name ?? 'سیستم',
            'status' => 'info',
            'timestamp' => strtotime($date),
        ];

        // Sort events descending
        usort($timelineEvents, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        $this->timeline = $timelineEvents;
    }

    public function loadClientCustomFields()
    {
        if (!$this->deal || !$this->deal->client) return;

        $client = $this->deal->client;
        $metaValues = $client->meta ?? [];

        if (class_exists(ClientForm::class)) {
            $form = ClientForm::default();
            if ($form && is_array($form->schema)) {
                $customFields = [];
                foreach ($form->schema as $field) {
                    $fieldId = $field['id'] ?? '';
                    $isSystem = $field['is_system'] ?? false;
                    
                    if (!$isSystem && !empty($fieldId)) {
                        $value = $metaValues[$fieldId] ?? null;
                        if ($value !== null) {
                            $customFields[] = [
                                'label' => $field['label'] ?? $fieldId,
                                'value' => is_array($value) ? implode('، ', $value) : $value,
                            ];
                        }
                    }
                }
                $this->clientCustomFields = $customFields;
            }
        }
    }

    public function addFollowUp()
    {
        $this->validate([
            'newTaskTitle' => 'required|string|max:191',
            'newTaskDueAt' => 'required|date',
            'newTaskPriority' => 'required|in:LOW,MEDIUM,HIGH,CRITICAL',
        ]);

        Task::create([
            'title' => $this->newTaskTitle,
            'description' => $this->newTaskDescription,
            'task_type' => Task::TYPE_FOLLOW_UP,
            'assignee_id' => $this->deal->user_id ?? auth()->id(),
            'creator_id' => auth()->id(),
            'status' => Task::STATUS_TODO,
            'priority' => $this->newTaskPriority,
            'due_at' => $this->newTaskDueAt,
            'related_type' => 'DEAL',
            'related_id' => $this->dealId,
        ]);

        $this->newTaskTitle = '';
        $this->newTaskDescription = '';
        $this->newTaskDueAt = null;
        $this->newTaskPriority = 'MEDIUM';

        $this->loadTimeline();
        $this->dispatch('notify', message: 'پیگیری جدید با موفقیت ثبت شد.', type: 'success');
    }

    public function completeTask(int $taskId)
    {
        $task = Task::find($taskId);
        if ($task) {
            $task->status = Task::STATUS_DONE;
            $task->completed_at = now();
            $task->save();

            $this->loadTimeline();
            $this->dispatch('notify', message: 'پیگیری انجام شد.', type: 'success');
        }
    }

    public function openCloseModal(string $type)
    {
        if (!in_array($type, ['won', 'lost'])) return;

        $this->closeType = $type;
        $this->closingRevenue = (float) ($this->deal->expected_revenue ?? 0.0);
        $this->closingLossReasonId = null;
        $this->showCloseModal = true;
    }

    public function closeDeal()
    {
        if ($this->closeType === 'won') {
            $this->validate([
                'closingRevenue' => 'required|numeric|min:0',
            ]);
        } else {
            $this->validate([
                'closingLossReasonId' => 'required|exists:sales_loss_reasons,id',
            ]);
        }

        $deal = SalesDeal::findOrFail($this->dealId);

        if ($this->closeType === 'won') {
            $wonStage = SalesPipeline::where('is_won', true)->first();
            if (!$wonStage) {
                $wonStage = SalesPipeline::create(['name' => 'موفق (Won)', 'color' => '#22c55e', 'order' => 5, 'is_won' => true]);
            }

            $deal->status = 'won';
            $deal->actual_revenue = $this->closingRevenue;
            $deal->pipeline_stage_id = $wonStage->id;
            $deal->loss_reason_id = null;
        } else {
            $lostStage = SalesPipeline::where('is_lost', true)->first();
            if (!$lostStage) {
                $lostStage = SalesPipeline::create(['name' => 'ناموفق (Lost)', 'color' => '#ef4444', 'order' => 6, 'is_lost' => true]);
            }

            $deal->status = 'lost';
            $deal->actual_revenue = 0.0;
            $deal->pipeline_stage_id = $lostStage->id;
            $deal->loss_reason_id = $this->closingLossReasonId;
        }

        $deal->stage_entered_at = now();
        $deal->save();

        $this->showCloseModal = false;
        $this->loadDeal();
        $this->loadTimeline();
        
        // Forget statistics cache to reflect immediately
        $userId = auth()->id();
        \Illuminate\Support\Facades\Cache::forget("cockpit_stats_user_{$userId}");

        $this->dispatch('notify', message: 'وضعیت پرونده فروش با موفقیت تغییر کرد.', type: 'success');
    }

    public function render()
    {
        $openTasks = Task::where('related_id', $this->dealId)
            ->where('related_type', 'DEAL')
            ->whereIn('status', [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS])
            ->orderBy('due_at')
            ->get();

        $stages = SalesPipeline::orderBy('order')->get();
        $lossReasons = \Modules\Sales\App\Models\SalesLossReason::where('is_active', true)->get();

        return view('sales::livewire.deal-360-view', [
            'openTasks' => $openTasks,
            'stages' => $stages,
            'lossReasons' => $lossReasons,
        ]);
    }
}
