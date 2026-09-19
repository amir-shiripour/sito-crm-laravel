<?php

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Modules\Tasks\Entities\Task;
use Modules\FollowUps\Entities\FollowUp;
use App\Models\User;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;
use Morilog\Jalali\CalendarUtils;
use Modules\Clients\Entities\Client;
use Modules\Sales\App\Models\SalesDeal;

class TaskTab extends Component
{
    use WithPagination;

    // Filters
    public string $filterType = 'all';      // 'all', 'followup', 'general'
    public string $filterStatus = '';       // '', 'todo', 'in_progress', 'done', 'cancelled', 'overdue'
    public string $filterDate = 'all';      // 'today', 'week', 'month', 'all'
    public string $filterPriority = '';     // 'LOW', 'MEDIUM', 'HIGH', 'CRITICAL', ''
    public string $viewMode = 'list';       // 'list', 'kanban'
    public string $search = '';
    public string $filterClientMode = 'active'; // 'active', 'all'

    public ?int $selectedClientId = null;
    public ?string $selectedClientName = null;
    public ?int $selectedDealId = null;
    public ?int $editingTaskId = null;
    public bool $showCreateModal = false;

    // Client search in modal
    public string $clientSearch = '';
    public array $clientSearchResults = [];
    public ?int $modalClientId = null;
    public ?string $modalClientName = null;

    // Form fields
    #[Validate('required|string|min:3|max:255', message: 'لطفاً عنوان پیگیری را وارد کنید (حداقل ۳ کاراکتر)')]
    public string $title = '';

    #[Validate('nullable|string')]
    public ?string $description = null;

    #[Validate('required|in:FOLLOW_UP,GENERAL')]
    public string $taskType = 'FOLLOW_UP';

    #[Validate('required|in:LOW,MEDIUM,HIGH,CRITICAL')]
    public string $taskPriority = 'MEDIUM';

    public ?string $due_date_jalali = null;
    public ?string $due_time = null;

    #[Validate('nullable|exists:users,id')]
    public ?int $assignee_id = null;

    public ?int $deal_id = null;

    public function mount($selectedClientId = null)
    {
        $this->selectedClientId = $selectedClientId ? (int) $selectedClientId : null;
        $this->loadSelectedClientData();
        $this->due_date_jalali = Jalalian::now()->format('Y/m/d');
        $this->due_time = now()->addHour()->format('H:i');
        $this->assignee_id = auth()->id();

        if ($this->selectedClientId) {
            $this->search = '';
        }
    }

    #[On('clientChanged')]
    public function updateSelectedClient($clientId)
    {
        $this->selectedClientId = $clientId ? (int) $clientId : null;
        $this->filterClientMode = 'active';
        $this->loadSelectedClientData();
        if ($this->selectedClientId) {
            $this->search = '';
        }
        $this->resetPage();
    }

    #[On('dealChanged')]
    public function updateSelectedDeal($dealId)
    {
        $this->selectedDealId = $dealId ? (int) $dealId : null;
        $this->deal_id = $this->selectedDealId;
    }

    #[On('createFollowupFromCall')]
    public function openCreateModalFromCall($data)
    {
        $this->resetErrorBag();
        $clientId = $data['client_id'] ?? $this->selectedClientId;
        $this->modalClientId = $clientId ? (int) $clientId : null;
        if ($this->modalClientId && class_exists(Client::class)) {
            $client = Client::find($this->modalClientId);
            $this->modalClientName = $client ? $client->full_name : null;
        }

        $this->title = $data['title'] ?? 'پیگیری پیرو تماس تلفنی';
        $this->taskPriority = 'HIGH';
        $this->description = $data['description'] ?? 'برنامه‌ریزی شده به دنبال تماس اخیر با مشتری.';
        $this->taskType = 'FOLLOW_UP';
        $this->assignee_id = auth()->id();
        $this->deal_id = $data['deal_id'] ?? $this->selectedDealId;

        if (!empty($data['due_date'])) {
            try {
                $c = Carbon::parse($data['due_date']);
                $this->due_date_jalali = Jalalian::fromCarbon($c)->format('Y/m/d');
                $this->due_time = $c->format('H:i');
            } catch (\Throwable $e) {
                $this->due_date_jalali = Jalalian::now()->addDays(1)->format('Y/m/d');
                $this->due_time = '10:00';
            }
        } else {
            $this->due_date_jalali = Jalalian::now()->addDays(1)->format('Y/m/d');
            $this->due_time = '10:00';
        }

        $this->autoSelectDeal();
        $this->showCreateModal = true;
    }

    protected function loadSelectedClientData()
    {
        if ($this->selectedClientId && class_exists(Client::class)) {
            $client = Client::find($this->selectedClientId);
            $this->selectedClientName = $client ? $client->full_name : null;
            $this->modalClientId = $this->selectedClientId;
            $this->modalClientName = $this->selectedClientName;
            $this->autoSelectDeal();
        } else {
            $this->selectedClientName = null;
        }
    }

    public function updatedClientSearch($value)
    {
        $value = trim($value);
        if (mb_strlen($value) < 2) {
            $this->clientSearchResults = [];
            return;
        }

        if (class_exists(Client::class)) {
            $user = auth()->user();
            $query = Client::query();
            if (method_exists(Client::class, 'scopeVisibleForUser')) {
                $query->visibleForUser($user);
            }
            $this->clientSearchResults = $query
                ->where(function ($q) use ($value) {
                    $q->where('full_name', 'like', "%{$value}%")
                      ->orWhere('phone', 'like', "%{$value}%")
                      ->orWhere('national_code', 'like', "%{$value}%")
                      ->orWhere('case_number', 'like', "%{$value}%");
                })
                ->limit(8)
                ->get(['id', 'full_name', 'phone', 'case_number'])
                ->map(fn($c) => [
                    'id' => $c->id,
                    'full_name' => $c->full_name,
                    'phone' => $c->phone,
                    'case_number' => $c->case_number,
                ])
                ->toArray();
        }
    }

    public function selectClientForModal(int $clientId)
    {
        $this->modalClientId = $clientId;
        if (class_exists(Client::class)) {
            $client = Client::find($clientId);
            $this->modalClientName = $client ? $client->full_name : null;
        }
        $this->clientSearchResults = [];
        $this->clientSearch = '';
        $this->autoSelectDeal();
    }

    public function clearModalClient()
    {
        $this->modalClientId = null;
        $this->modalClientName = null;
        $this->clientSearch = '';
        $this->clientSearchResults = [];
        $this->deal_id = null;
    }

    protected function autoSelectDeal(): void
    {
        $targetClientId = $this->modalClientId ?: $this->selectedClientId;
        if ($targetClientId && class_exists(SalesDeal::class)) {
            $openDeal = SalesDeal::where('client_id', $targetClientId)
                ->where('status', 'open')
                ->latest('updated_at')
                ->first();

            if ($openDeal) {
                $this->deal_id = $openDeal->id;
            } elseif ($this->selectedDealId) {
                $this->deal_id = $this->selectedDealId;
            }
        } elseif ($this->selectedDealId) {
            $this->deal_id = $this->selectedDealId;
        }
    }

    public function openCreateModal()
    {
        $this->resetErrorBag();
        $this->reset(['title', 'description', 'editingTaskId', 'clientSearch']);
        $this->clientSearchResults = [];
        $this->taskType = 'FOLLOW_UP';
        $this->taskPriority = 'MEDIUM';
        $this->due_date_jalali = Jalalian::now()->format('Y/m/d');
        $this->due_time = now()->addHour()->format('H:i');
        $this->assignee_id = auth()->id();

        if ($this->selectedClientId) {
            $this->modalClientId = $this->selectedClientId;
            $this->modalClientName = $this->selectedClientName;
        } else {
            $this->modalClientId = null;
            $this->modalClientName = null;
        }

        $this->autoSelectDeal();
        $this->showCreateModal = true;
    }

    public function editTask($id)
    {
        $this->resetErrorBag();
        $task = Task::findOrFail($id);
        $this->editingTaskId = $task->id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->taskType = $task->task_type ?: 'FOLLOW_UP';
        $this->taskPriority = $task->priority ?: 'MEDIUM';
        $this->assignee_id = $task->assignee_id;

        if ($task->due_at) {
            $this->due_date_jalali = Jalalian::fromCarbon($task->due_at)->format('Y/m/d');
            $this->due_time = $task->due_at->format('H:i');
        } else {
            $this->due_date_jalali = Jalalian::now()->format('Y/m/d');
            $this->due_time = now()->format('H:i');
        }

        if ($task->related_type === Task::RELATED_TYPE_CLIENT && $task->related_id) {
            $this->modalClientId = (int) $task->related_id;
            if (class_exists(Client::class)) {
                $client = Client::find($this->modalClientId);
                $this->modalClientName = $client ? $client->full_name : null;
            }
        } else {
            $this->modalClientId = null;
            $this->modalClientName = null;
        }

        $meta = $task->meta ?? [];
        $this->deal_id = $meta['deal_id'] ?? null;

        $this->showCreateModal = true;
    }

    private function normalizeJalaliDigits(?string $value): ?string
    {
        if ($value === null) return null;
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $latin   = ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'];
        return str_replace($persian, $latin, $value);
    }

    private function convertJalaliToCarbon(?string $jalaliDate, ?string $time = null): ?Carbon
    {
        if (empty($jalaliDate)) return null;

        try {
            $jalaliDate = $this->normalizeJalaliDigits(trim($jalaliDate));
            $parts = preg_split('/[^\d]+/', $jalaliDate);
            if (count($parts) < 3) return null;

            [$jy, $jm, $jd] = array_map('intval', array_slice($parts, 0, 3));
            [$gy, $gm, $gd] = CalendarUtils::toGregorian($jy, $jm, $jd);

            $hour = 12;
            $minute = 0;
            if ($time !== null) {
                $time = $this->normalizeJalaliDigits(trim($time));
                $tParts = preg_split('/[^\d]+/', $time);
                if (count($tParts) >= 2) {
                    $h = (int) $tParts[0];
                    $m = (int) $tParts[1];
                    if ($h >= 0 && $h <= 23) $hour = $h;
                    if ($m >= 0 && $m <= 59) $minute = $m;
                }
            }

            return Carbon::create($gy, $gm, $gd, $hour, $minute, 0);
        } catch (\Throwable $e) {
            logger()->warning('TaskTab: convertJalaliToCarbon failed', [
                'jalali' => $jalaliDate,
                'time'   => $time,
                'error'  => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function saveTask()
    {
        $this->validate();

        $dueAtCarbon = $this->convertJalaliToCarbon($this->due_date_jalali, $this->due_time);

        $clientId = $this->modalClientId ?: $this->selectedClientId;

        $meta = [];
        if ($this->deal_id) {
            $meta['deal_id'] = (int) $this->deal_id;
        }

        $data = [
            'title'        => $this->title,
            'description'  => $this->description,
            'task_type'    => $this->taskType,
            'priority'     => $this->taskPriority,
            'due_at'       => $dueAtCarbon,
            'assignee_id'  => $this->assignee_id ?: auth()->id(),
        ];

        if ($clientId) {
            $data['related_type'] = Task::RELATED_TYPE_CLIENT;
            $data['related_id']   = $clientId;
        } else {
            $data['related_type'] = null;
            $data['related_id']   = null;
        }

        if ($this->editingTaskId) {
            $task = Task::findOrFail($this->editingTaskId);
            $existingMeta = $task->meta ?? [];
            $data['meta'] = array_merge($existingMeta, $meta);
            $task->update($data);
            $message = 'پیگیری با موفقیت ویرایش شد.';
        } else {
            $data['creator_id'] = auth()->id();
            $data['status']     = Task::STATUS_TODO;
            $data['meta']       = $meta;

            if ($this->taskType === 'FOLLOW_UP' && class_exists(FollowUp::class)) {
                $task = FollowUp::create($data);
            } else {
                $task = Task::create($data);
            }
            $message = 'پیگیری جدید با موفقیت ثبت شد.';
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
        $this->dispatch('notify', message: 'پیگیری با موفقیت انجام شد.', type: 'success');
    }

    public function cancelTask($id)
    {
        $task = Task::findOrFail($id);
        $task->update(['status' => Task::STATUS_CANCELED]);
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'پیگیری لغو شد.', type: 'info');
    }

    public function assignToMe($id)
    {
        $task = Task::findOrFail($id);
        $task->update(['assignee_id' => auth()->id()]);
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'پیگیری به شما تخصیص یافت.', type: 'success');
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

    protected function applyClientScope($query)
    {
        if ($this->selectedClientId && $this->filterClientMode === 'active') {
            $clientDealIds = [];
            if (class_exists(SalesDeal::class)) {
                $clientDealIds = SalesDeal::where('client_id', $this->selectedClientId)->pluck('id')->toArray();
            }

            $query->where(function ($q) use ($clientDealIds) {
                $q->where(function ($sub) {
                    $sub->where('related_type', Task::RELATED_TYPE_CLIENT)
                        ->where('related_id', $this->selectedClientId);
                });

                if (!empty($clientDealIds)) {
                    $q->orWhere(function ($sub) use ($clientDealIds) {
                        $sub->where('related_type', 'DEAL')
                            ->whereIn('related_id', $clientDealIds);
                    })->orWhere(function ($sub) use ($clientDealIds) {
                        $sub->whereIn('meta->deal_id', $clientDealIds);
                    });
                }
            });
        }

        return $query;
    }

    public function getFollowupKpisProperty(): array
    {
        $base = Task::query();

        $this->applyClientScope($base);

        $todayTotal = (clone $base)->whereDate('due_at', today())->count();
        $todayDone = (clone $base)->where('status', Task::STATUS_DONE)
            ->where(function($q) {
                $q->whereDate('completed_at', today())
                  ->orWhere(function($sub) {
                      $sub->whereNull('completed_at')->whereDate('due_at', today());
                  });
            })->count();
        $todayPending = (clone $base)->whereIn('status', [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS])
            ->whereDate('due_at', today())
            ->count();
        $overdue = (clone $base)->where('due_at', '<', now())
            ->whereNotIn('status', [Task::STATUS_DONE, Task::STATUS_CANCELED])
            ->count();

        return [
            'today_total'   => $todayTotal,
            'today_done'    => $todayDone,
            'today_pending' => $todayPending,
            'overdue'       => $overdue,
        ];
    }

    protected function getBaseQuery()
    {
        $query = Task::query()->with(['assignee', 'creator', 'relatedClient']);

        $this->applyClientScope($query);

        if ($this->filterType !== 'all') {
            $query->where('task_type', strtoupper($this->filterType));
        }

        if ($this->filterStatus === 'active') {
            $query->whereIn('status', [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS]);
        } elseif ($this->filterStatus === 'overdue') {
            $query->where('due_at', '<', now())
                  ->whereNotIn('status', [Task::STATUS_DONE, Task::STATUS_CANCELED]);
        } elseif ($this->filterStatus !== '') {
            $query->where('status', strtoupper($this->filterStatus));
        }

        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }

        if ($this->filterDate === 'today') {
            $query->whereDate('due_at', today());
        } elseif ($this->filterDate === 'week') {
            $query->whereBetween('due_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($this->filterDate === 'month') {
            $query->whereBetween('due_at', [now()->startOfMonth(), now()->endOfMonth()]);
        }

        // Search is only active when not in scoped active client mode or when typed explicitly
        if ($this->search && (!$this->selectedClientId || $this->filterClientMode !== 'active')) {
            $query->where(function($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                  ->orWhere('description', 'like', '%'.$this->search.'%')
                  ->orWhereHas('relatedClient', function($cQuery) {
                      $cQuery->where('full_name', 'like', '%'.$this->search.'%')
                             ->orWhere('phone', 'like', '%'.$this->search.'%');
                  });
            });
        }

        return $query;
    }

    public function render()
    {
        $users = User::all();
        $selectedClient = $this->selectedClientId && class_exists(Client::class)
            ? Client::find($this->selectedClientId)
            : null;

        // Fetch client deals if client selected for the modal
        $targetClientId = $this->modalClientId ?: $this->selectedClientId;
        $clientDeals = collect();
        if ($targetClientId && class_exists(SalesDeal::class)) {
            $clientDeals = SalesDeal::where('client_id', $targetClientId)
                ->orderByDesc('updated_at')
                ->get(['id', 'title', 'status', 'expected_revenue']);
        }

        $kpis = $this->followupKpis;

        $priorityOrderSql = "CASE WHEN status = 'DONE' OR status = 'CANCELED' THEN 1 ELSE 0 END ASC,
        CASE priority 
            WHEN 'CRITICAL' THEN 1 
            WHEN 'HIGH' THEN 2 
            WHEN 'MEDIUM' THEN 3 
            WHEN 'LOW' THEN 4 
            ELSE 5 
        END ASC, 
        CASE WHEN due_at IS NULL THEN 1 ELSE 0 END ASC, 
        due_at ASC,
        id DESC";

        if ($this->viewMode === 'kanban') {
            $baseQuery = $this->getBaseQuery();
            $tasks = $baseQuery->orderByRaw($priorityOrderSql)->get();

            $kanbanTasks = [
                'todo'        => $tasks->where('status', Task::STATUS_TODO),
                'in_progress' => $tasks->where('status', Task::STATUS_IN_PROGRESS),
                'done'        => $tasks->where('status', Task::STATUS_DONE),
            ];

            return view('sales::livewire.task-tab', [
                'kanbanTasks'      => $kanbanTasks,
                'users'            => $users,
                'isKanban'         => true,
                'selectedClient'   => $selectedClient,
                'selectedClientId' => $this->selectedClientId,
                'clientDeals'      => $clientDeals,
                'kpis'             => $kpis,
                'viewMode'         => $this->viewMode,
                'filterStatus'     => $this->filterStatus,
                'filterDate'       => $this->filterDate,
                'filterPriority'   => $this->filterPriority,
                'filterClientMode' => $this->filterClientMode,
            ]);
        }

        // List view with pagination
        $tasks = $this->getBaseQuery()->orderByRaw($priorityOrderSql)->paginate(12);

        return view('sales::livewire.task-tab', [
            'tasks'            => $tasks,
            'users'            => $users,
            'isKanban'         => false,
            'selectedClient'   => $selectedClient,
            'selectedClientId' => $this->selectedClientId,
            'clientDeals'      => $clientDeals,
            'kpis'             => $kpis,
            'viewMode'         => $this->viewMode,
            'filterStatus'     => $this->filterStatus,
            'filterDate'       => $this->filterDate,
            'filterPriority'   => $this->filterPriority,
            'filterClientMode' => $this->filterClientMode,
        ]);
    }
}
