<?php

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Modules\Sales\App\Models\Campaign;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Modules\ClientCalls\Entities\ClientCall;

class CallCenterTab extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterDate = 'all'; // 'today', 'week', 'month', 'all'
    public ?int $selectedClientId = null;
    public ?int $editingCallId = null;
    
    // Create new call fields
    #[Validate('nullable|exists:sales_campaigns,id')]
    public ?int $campaign_id = null;
    
    #[Validate('required|date')]
    public ?string $call_date = null;
    
    #[Validate('nullable')]
    public ?string $call_time = null;
    
    #[Validate('nullable|integer')]
    public ?int $duration_seconds = null;
    
    #[Validate('required|in:inbound,outbound')]
    public string $direction = 'outbound';
    
    #[Validate('required|in:planned,answered,no_answer,busy,cancelled,failed')]
    public string $status = 'planned';
    
    #[Validate('nullable|string|max:255')]
    public ?string $reason = null;
    
    #[Validate('nullable|string')]
    public ?string $result = null;
    
    #[Validate('nullable|string|max:255')]
    public ?string $next_action = null;
    
    #[Validate('nullable|date')]
    public ?string $next_action_date = null;
    
    #[Validate('nullable|string')]
    public ?string $contact_phone = null;
    
    #[Validate('nullable|string')]
    public ?string $notes = null;
    
    public bool $showCreateModal = false;

    public function mount($selectedClientId = null)
    {
        $this->selectedClientId = $selectedClientId;
        $this->call_date = today()->format('Y-m-d');
        $this->call_time = now()->format('H:i');
    }

    #[On('clientChanged')]
    public function updateSelectedClient($clientId)
    {
        $this->selectedClientId = $clientId;
        if ($clientId && class_exists(\Modules\Clients\Entities\Client::class)) {
            $client = \Modules\Clients\Entities\Client::find($clientId);
            if ($client) {
                $this->contact_phone = $client->phone;
            }
        }
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetErrorBag();
        $this->reset([
            'campaign_id', 'duration_seconds', 'reason', 'result', 
            'next_action', 'next_action_date', 'notes', 'editingCallId'
        ]);
        $this->call_date = today()->format('Y-m-d');
        $this->call_time = now()->format('H:i');
        $this->direction = 'outbound';
        $this->status = 'planned';
        
        if ($this->selectedClientId && class_exists(\Modules\Clients\Entities\Client::class)) {
            $client = \Modules\Clients\Entities\Client::find($this->selectedClientId);
            if ($client) {
                $this->contact_phone = $client->phone;
            }
        }
        
        $this->showCreateModal = true;
    }

    public function editCall($id)
    {
        $this->resetErrorBag();
        $call = ClientCall::findOrFail($id);
        $this->editingCallId = $call->id;
        $this->selectedClientId = $call->client_id;
        $this->campaign_id = $call->campaign_id;
        $this->call_date = $call->call_date ? $call->call_date->format('Y-m-d') : today()->format('Y-m-d');
        $this->call_time = $call->call_time ? $call->call_time->format('H:i') : null;
        $this->duration_seconds = $call->duration_seconds;
        $this->direction = $call->direction ?? 'outbound';
        $this->status = $call->status;
        $this->reason = $call->reason;
        $this->result = $call->result;
        $this->next_action = $call->next_action;
        $this->next_action_date = $call->next_action_date ? $call->next_action_date->format('Y-m-d') : null;
        $this->contact_phone = $call->contact_phone;
        $this->notes = $call->notes;

        $this->showCreateModal = true;
    }

    public function saveCall()
    {
        $this->validate();

        if ($this->editingCallId) {
            $this->updateCall();
            return;
        }

        $call = ClientCall::create([
            'client_id' => $this->selectedClientId,
            'campaign_id' => $this->campaign_id,
            'user_id' => auth()->id(),
            'call_date' => $this->call_date,
            'call_time' => $this->call_time,
            'duration_seconds' => $this->duration_seconds,
            'direction' => $this->direction,
            'status' => $this->status,
            'reason' => $this->reason ?: 'تماس خروجی',
            'result' => $this->result,
            'next_action' => $this->next_action,
            'next_action_date' => $this->next_action_date,
            'contact_phone' => $this->contact_phone,
            'notes' => $this->notes,
        ]);

        // Auto-create Follow-up task if next_action is provided
        if ($this->next_action && $this->next_action_date && class_exists(\Modules\Tasks\Entities\Task::class)) {
            \Modules\Tasks\Entities\Task::create([
                'title' => $this->next_action,
                'description' => 'پیگیری بابت تماس ثبت شده در تاریخ ' . $this->call_date,
                'task_type' => \Modules\Tasks\Entities\Task::TYPE_FOLLOW_UP,
                'assignee_id' => auth()->id(),
                'creator_id' => auth()->id(),
                'status' => \Modules\Tasks\Entities\Task::STATUS_TODO,
                'priority' => \Modules\Tasks\Entities\Task::PRIORITY_MEDIUM,
                'due_at' => $this->next_action_date . ' 10:00:00',
                'related_type' => \Modules\Tasks\Entities\Task::RELATED_TYPE_CLIENT,
                'related_id' => $this->selectedClientId,
            ]);
        }

        $this->showCreateModal = false;
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'تماس با موفقیت ثبت شد', type: 'success');
    }

    protected function updateCall()
    {
        $call = ClientCall::findOrFail($this->editingCallId);
        $call->update([
            'campaign_id' => $this->campaign_id,
            'call_date' => $this->call_date,
            'call_time' => $this->call_time,
            'duration_seconds' => $this->duration_seconds,
            'direction' => $this->direction,
            'status' => $this->status,
            'reason' => $this->reason ?: 'تماس خروجی',
            'result' => $this->result,
            'next_action' => $this->next_action,
            'next_action_date' => $this->next_action_date,
            'contact_phone' => $this->contact_phone,
            'notes' => $this->notes,
        ]);

        if ($this->next_action && $this->next_action_date && class_exists(\Modules\Tasks\Entities\Task::class)) {
            \Modules\Tasks\Entities\Task::create([
                'title' => $this->next_action,
                'description' => 'پیگیری بابت تماس ویرایش شده در تاریخ ' . $this->call_date,
                'task_type' => \Modules\Tasks\Entities\Task::TYPE_FOLLOW_UP,
                'assignee_id' => auth()->id(),
                'creator_id' => auth()->id(),
                'status' => \Modules\Tasks\Entities\Task::STATUS_TODO,
                'priority' => \Modules\Tasks\Entities\Task::PRIORITY_MEDIUM,
                'due_at' => $this->next_action_date . ' 10:00:00',
                'related_type' => \Modules\Tasks\Entities\Task::RELATED_TYPE_CLIENT,
                'related_id' => $this->selectedClientId,
            ]);
        }

        $this->showCreateModal = false;
        $this->editingCallId = null;
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'تماس با موفقیت ویرایش شد', type: 'success');
    }

    public function deleteCall($id)
    {
        $call = ClientCall::findOrFail($id);
        $call->delete();
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'تماس با موفقیت حذف شد', type: 'success');
    }

    public function startQuickCall($status)
    {
        if (!$this->selectedClientId) {
            $this->dispatch('notify', message: 'لطفاً ابتدا یک مشتری را انتخاب کنید.', type: 'warning');
            return;
        }

        if (class_exists(\Modules\Clients\Entities\Client::class)) {
            $client = \Modules\Clients\Entities\Client::find($this->selectedClientId);
            if ($client) {
                ClientCall::create([
                    'client_id' => $this->selectedClientId,
                    'user_id' => auth()->id(),
                    'call_date' => today()->format('Y-m-d'),
                    'call_time' => now()->format('H:i'),
                    'direction' => 'outbound',
                    'status' => $status,
                    'reason' => 'تماس سریع خروجی',
                    'contact_phone' => $client->phone,
                ]);

                $this->dispatch('refreshStats');
                $this->dispatch('notify', message: 'تماس سریع ثبت شد', type: 'success');
            }
        }
    }

    public function cancelEditing()
    {
        $this->showCreateModal = false;
        $this->editingCallId = null;
    }

    public function selectClient($id)
    {
        $this->dispatch('clientSelected', $id);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterDate()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = ClientCall::query()
            ->with(['client', 'user'])
            ->visibleForUser(auth()->user());

        if ($this->selectedClientId) {
            $query->where('client_id', $this->selectedClientId);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterDate === 'today') {
            $query->today();
        } elseif ($this->filterDate === 'week') {
            $query->thisWeek();
        } elseif ($this->filterDate === 'month') {
            $query->whereMonth('call_date', now()->month)
                  ->whereYear('call_date', now()->year);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('reason', 'like', '%'.$this->search.'%')
                  ->orWhere('result', 'like', '%'.$this->search.'%')
                  ->orWhere('contact_phone', 'like', '%'.$this->search.'%')
                  ->orWhere('notes', 'like', '%'.$this->search.'%');
            });
        }

        $calls = $query->latest('call_date')->latest('call_time')->paginate(10);
        $campaigns = Campaign::where('status', 'active')->get();

        return view('sales::livewire.call-center-tab', [
            'calls' => $calls,
            'campaigns' => $campaigns
        ]);
    }
}
