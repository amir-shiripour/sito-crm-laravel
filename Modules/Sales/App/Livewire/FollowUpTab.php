<?php

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Modules\Sales\App\Models\SalesFollowUp;
use Modules\Sales\App\Models\Campaign;
use Modules\Sales\App\Models\SalesCall;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class FollowUpTab extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'open';
    public string $priority = '';
    public ?int $selectedClientId = null;

    // Create fields
    #[Validate('required|string|max:255')]
    public string $title = '';
    
    #[Validate('nullable|string')]
    public ?string $description = null;
    
    #[Validate('required|in:low,medium,high,urgent')]
    public string $followup_priority = 'medium';
    
    #[Validate('required')]
    public ?string $due_date = null;
    
    #[Validate('nullable')]
    public ?string $reminder_at = null;
    
    #[Validate('nullable|exists:sales_campaigns,id')]
    public ?int $campaign_id = null;
    
    #[Validate('nullable|exists:sales_calls,id')]
    public ?int $call_id = null;

    public bool $showCreateModal = false;

    public function mount($selectedClientId = null)
    {
        $this->selectedClientId = $selectedClientId;
        $this->due_date = now()->addDays(1)->format('Y-m-d H:i');
    }

    #[On('clientChanged')]
    public function updateSelectedClient($clientId)
    {
        \Log::info('FollowUpTab: updateSelectedClient called with ID: ' . var_export($clientId, true));
        $this->selectedClientId = $clientId;
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetErrorBag();
        $this->reset(['title', 'description', 'campaign_id', 'call_id', 'reminder_at']);
        $this->followup_priority = 'medium';
        $this->due_date = now()->addDays(1)->format('Y-m-d\TH:i');
        $this->showCreateModal = true;
    }

    #[On('createFollowupFromCall')]
    public function openCreateModalFromCall($data)
    {
        $this->resetErrorBag();
        $this->selectedClientId = $data['client_id'] ?? $this->selectedClientId;
        $this->call_id = $data['call_id'] ?? null;
        $this->title = $data['title'] ?? '';
        $this->due_date = isset($data['due_date']) ? date('Y-m-d\TH:i', strtotime($data['due_date'])) : now()->addDays(1)->format('Y-m-d\TH:i');
        $this->followup_priority = 'high';
        $this->description = 'برنامه‌ریزی شده به دنبال تماس قبلی';
        $this->showCreateModal = true;
    }

    public function saveFollowUp()
    {
        $this->validate();

        if (class_exists(\Modules\FollowUps\Entities\FollowUp::class) && \Schema::hasTable('tasks')) {
            $priorityMap = [
                'low' => 'LOW',
                'medium' => 'MEDIUM',
                'high' => 'HIGH',
                'urgent' => 'CRITICAL'
            ];
            \Modules\FollowUps\Entities\FollowUp::create([
                'related_type' => 'CLIENT',
                'related_id' => $this->selectedClientId,
                'assignee_id' => auth()->id(),
                'creator_id' => auth()->id(),
                'title' => $this->title,
                'description' => $this->description,
                'status' => 'TODO',
                'priority' => $priorityMap[$this->followup_priority] ?? 'MEDIUM',
                'due_at' => $this->due_date,
            ]);
        } else {
            SalesFollowUp::create([
                'client_id' => $this->selectedClientId,
                'campaign_id' => $this->campaign_id,
                'call_id' => $this->call_id,
                'user_id' => auth()->id(),
                'title' => $this->title,
                'description' => $this->description,
                'status' => 'open',
                'priority' => $this->followup_priority,
                'due_date' => $this->due_date,
                'reminder_at' => $this->reminder_at ? $this->reminder_at : null,
            ]);
        }

        $this->showCreateModal = false;
        $this->dispatch('refreshStats');
    }

    public function completeFollowUp($id)
    {
        if (class_exists(\Modules\FollowUps\Entities\FollowUp::class) && \Schema::hasTable('tasks')) {
            $followUp = \Modules\FollowUps\Entities\FollowUp::find($id);
            if ($followUp) {
                $followUp->update([
                    'status' => 'DONE',
                ]);
                $this->dispatch('refreshStats');
                return;
            }
        }
        
        $followUp = SalesFollowUp::find($id);
        if ($followUp) {
            $followUp->update([
                'status' => 'done',
                'completed_at' => now(),
            ]);
            $this->dispatch('refreshStats');
        }
    }

    public function cancelFollowUp($id)
    {
        if (class_exists(\Modules\FollowUps\Entities\FollowUp::class) && \Schema::hasTable('tasks')) {
            $followUp = \Modules\FollowUps\Entities\FollowUp::find($id);
            if ($followUp) {
                $followUp->update([
                    'status' => 'CANCELED',
                ]);
                $this->dispatch('refreshStats');
                return;
            }
        }

        $followUp = SalesFollowUp::find($id);
        if ($followUp) {
            $followUp->update([
                'status' => 'cancelled',
            ]);
            $this->dispatch('refreshStats');
        }
    }

    public function selectClient($id)
    {
        $this->dispatch('clientSelected', $id);
    }

    public function render()
    {
        \Log::info('FollowUpTab: render called. selectedClientId = ' . var_export($this->selectedClientId, true));
        
        if (class_exists(\Modules\FollowUps\Entities\FollowUp::class) && \Schema::hasTable('tasks')) {
            $query = \Modules\FollowUps\Entities\FollowUp::query();
            
            if ($this->selectedClientId) {
                $query->where('related_type', 'CLIENT')->where('related_id', $this->selectedClientId);
            }

            if ($this->status && $this->status != 'all') {
                $statusMap = [
                    'open' => 'TODO',
                    'in_progress' => 'IN_PROGRESS',
                    'done' => 'DONE',
                    'cancelled' => 'CANCELED'
                ];
                if (isset($statusMap[$this->status])) {
                    $query->where('status', $statusMap[$this->status]);
                }
            }

            if ($this->priority) {
                $priorityMap = [
                    'low' => 'LOW',
                    'medium' => 'MEDIUM',
                    'high' => 'HIGH',
                    'urgent' => 'CRITICAL'
                ];
                if (isset($priorityMap[$this->priority])) {
                    $query->where('priority', $priorityMap[$this->priority]);
                }
            }

            if ($this->search) {
                $query->where(function($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                      ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            }

            $followUps = $query->orderBy('due_at', 'asc')->paginate(8);
        } else {
            $query = SalesFollowUp::query()->with(['client', 'call', 'user', 'campaign']);

            if ($this->selectedClientId) {
                $query->where('client_id', $this->selectedClientId);
            }

            if ($this->status && $this->status != 'all') {
                $query->where('status', $this->status);
            }

            if ($this->priority) {
                $query->where('priority', $this->priority);
            }

            if ($this->search) {
                $query->where(function($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                      ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            }

            $followUps = $query->orderBy('due_date', 'asc')->paginate(8);
        }

        $campaigns = Campaign::where('status', 'active')->get();
        $calls = $this->selectedClientId ? SalesCall::where('client_id', $this->selectedClientId)->latest()->get() : collect();

        return view('sales::livewire.follow-up-tab', [
            'followups' => $followUps,
            'campaigns' => $campaigns,
            'calls' => $calls
        ]);
    }
}
