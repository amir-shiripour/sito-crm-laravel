<?php

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Modules\Tasks\Entities\Task;
use App\Models\User;

class TaskTab extends Component
{
    use WithPagination;

    // Filters
    public string $filterType = 'all';    // 'all', 'followup', 'general'
    public string $filterStatus = 'active'; // 'active', 'done', 'cancelled', 'all'
    public string $filterDate = 'all';    // 'today', 'week', 'all'
    public string $filterPriority = '';   // 'LOW', 'MEDIUM', 'HIGH', 'CRITICAL', ''
    public string $viewMode = 'list';     // 'list', 'kanban'
    public string $search = '';

    public ?int $selectedClientId = null;
    public ?int $editingTaskId = null;
    public bool $showCreateModal = false;

    // Task fields
    #[Validate('required|string|min:3|max:255')]
    public string $title = '';

    #[Validate('nullable|string')]
    public ?string $description = null;

    #[Validate('required|in:GENERAL,FOLLOW_UP')]
    public string $taskType = 'FOLLOW_UP';

    #[Validate('required|in:LOW,MEDIUM,HIGH,CRITICAL')]
    public string $taskPriority = 'MEDIUM';

    #[Validate('required|date_format:Y-m-d\TH:i')]
    public ?string $due_at = null;

    #[Validate('nullable|exists:users,id')]
    public ?int $assignee_id = null;

    public function mount($selectedClientId = null)
    {
        $this->selectedClientId = $selectedClientId;
        $this->due_at = now()->addDay()->format('Y-m-d\TH:i');
        $this->assignee_id = auth()->id();
    }

    #[On('clientChanged')]
    public function updateSelectedClient($clientId)
    {
        $this->selectedClientId = $clientId;
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetErrorBag();
        $this->reset(['title', 'description', 'editingTaskId']);
        $this->taskType = 'FOLLOW_UP';
        $this->taskPriority = 'MEDIUM';
        $this->due_at = now()->addDay()->format('Y-m-d\TH:i');
        $this->assignee_id = auth()->id();
        $this->showCreateModal = true;
    }

    public function editTask($id)
    {
        $this->resetErrorBag();
        $task = Task::findOrFail($id);
        $this->editingTaskId = $task->id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->taskType = $task->task_type;
        $this->taskPriority = $task->priority;
        $this->due_at = $task->due_at ? $task->due_at->format('Y-m-d\TH:i') : null;
        $this->assignee_id = $task->assignee_id;
        $this->showCreateModal = true;
    }

    public function saveTask()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'task_type' => $this->taskType,
            'priority' => $this->taskPriority,
            'due_at' => $this->due_at,
            'assignee_id' => $this->assignee_id,
        ];

        if ($this->selectedClientId) {
            $data['related_type'] = Task::RELATED_TYPE_CLIENT;
            $data['related_id'] = $this->selectedClientId;
        }

        if ($this->editingTaskId) {
            $task = Task::findOrFail($this->editingTaskId);
            $task->update($data);
            $message = 'وظیفه با موفقیت ویرایش شد.';
        } else {
            $data['creator_id'] = auth()->id();
            $data['status'] = Task::STATUS_TODO;
            $task = Task::create($data);
            $message = 'وظیفه جدید با موفقیت ایجاد شد.';
        }

        $this->showCreateModal = false;
        $this->editingTaskId = null;
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: $message, type: 'success');
    }

    public function completeTask($id)
    {
        $task = Task::findOrFail($id);
        $task->update(['status' => Task::STATUS_DONE]);
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'وظیفه با موفقیت انجام شد.', type: 'success');
    }

    public function cancelTask($id)
    {
        $task = Task::findOrFail($id);
        $task->update(['status' => Task::STATUS_CANCELED]);
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'وظیفه لغو شد.', type: 'success');
    }

    public function assignToMe($id)
    {
        $task = Task::findOrFail($id);
        $task->update(['assignee_id' => auth()->id()]);
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'وظیفه به شما واگذار شد.', type: 'success');
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'list' ? 'kanban' : 'list';
    }

    public function updatingFilterType() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingFilterDate() { $this->resetPage(); }
    public function updatingFilterPriority() { $this->resetPage(); }
    public function updatingSearch() { $this->resetPage(); }

    protected function getBaseQuery()
    {
        $query = Task::query()->with(['assignee', 'creator']);

        if ($this->selectedClientId) {
            $query->where('related_type', Task::RELATED_TYPE_CLIENT)
                  ->where('related_id', $this->selectedClientId);
        }

        if ($this->filterType !== 'all') {
            $query->where('task_type', strtoupper($this->filterType));
        }

        if ($this->filterStatus === 'active') {
            $query->whereIn('status', [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS]);
        } elseif ($this->filterStatus !== 'all') {
            $query->where('status', strtoupper($this->filterStatus));
        }

        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }

        if ($this->filterDate === 'today') {
            $query->whereDate('due_at', today());
        } elseif ($this->filterDate === 'week') {
            $query->whereBetween('due_at', [now()->startOfWeek(), now()->endOfWeek()]);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                  ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        return $query;
    }

    public function render()
    {
        $users = User::all();

        if ($this->viewMode === 'kanban') {
            // Kanban does not use default pagination, loads tasks categorized by status
            $baseQuery = $this->getBaseQuery();
            $tasks = $baseQuery->latest('due_at')->get();

            $kanbanTasks = [
                'todo' => $tasks->where('status', Task::STATUS_TODO),
                'in_progress' => $tasks->where('status', Task::STATUS_IN_PROGRESS),
                'done' => $tasks->where('status', Task::STATUS_DONE),
            ];

            return view('sales::livewire.task-tab', [
                'kanbanTasks' => $kanbanTasks,
                'users' => $users,
                'isKanban' => true,
            ]);
        }

        // List view with pagination
        $tasks = $this->getBaseQuery()->latest('due_at')->paginate(10);

        return view('sales::livewire.task-tab', [
            'tasks' => $tasks,
            'users' => $users,
            'isKanban' => false,
        ]);
    }
}
