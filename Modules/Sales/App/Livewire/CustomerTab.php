<?php

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class CustomerTab extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $selectedClientId = null;
    
    // New Client fields (mirroring Modules\Clients\Entities\Client table columns)
    #[Validate('required|string|unique:clients,username')]
    public string $username = '';
    
    #[Validate('required|string|max:255')]
    public string $full_name = '';
    
    #[Validate('nullable|email|unique:clients,email')]
    public ?string $email = null;
    
    #[Validate('required|string|unique:clients,phone')]
    public string $phone = '';
    
    #[Validate('nullable|string|max:10')]
    public ?string $national_code = null;
    
    #[Validate('nullable|string')]
    public ?string $case_number = null;
    
    #[Validate('nullable|string')]
    public ?string $notes = null;
    
    public string $filterStatus = 'all';
    public string $sortBy = 'latest';
    public bool $showCreateModal = false;

    public function mount($selectedClientId = null)
    {
        $this->selectedClientId = $selectedClientId;
    }

    #[On('clientChanged')]
    public function updateSelectedClient($clientId)
    {
        $this->selectedClientId = $clientId;
    }

    public function selectCustomer($id)
    {
        $this->selectedClientId = $id;
        $this->dispatch('clientSelected', clientId: $id);
    }

    public function openCreateModal()
    {
        $this->resetErrorBag();
        $this->reset(['username', 'full_name', 'email', 'phone', 'national_code', 'case_number', 'notes']);
        $this->showCreateModal = true;
    }

    public function saveCustomer()
    {
        $this->validate();

        if (class_exists(\Modules\Clients\Entities\Client::class)) {
            $client = \Modules\Clients\Entities\Client::create([
                'username' => $this->username,
                'full_name' => $this->full_name,
                'phone' => $this->phone,
                'email' => $this->email,
                'national_code' => $this->national_code,
                'case_number' => $this->case_number,
                'notes' => $this->notes,
                'created_by' => auth()->id(),
            ]);

            $this->showCreateModal = false;
            $this->dispatch('refreshStats');
            $this->selectCustomer($client->id);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }

    public function render()
    {
        $clients = collect();
        $statuses = collect();

        if (class_exists(\Modules\Clients\Entities\ClientStatus::class)) {
            $statuses = \Modules\Clients\Entities\ClientStatus::active()->get();
        }
        
        if (class_exists(\Modules\Clients\Entities\Client::class)) {
            $query = \Modules\Clients\Entities\Client::query()
                ->visibleForUser(auth()->user())
                ->with(['status']);
            
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('full_name', 'like', '%'.$this->search.'%')
                      ->orWhere('phone', 'like', '%'.$this->search.'%')
                      ->orWhere('case_number', 'like', '%'.$this->search.'%');
                });
            }

            if ($this->filterStatus !== 'all') {
                $query->where('status_id', $this->filterStatus);
            }
            
            if ($this->sortBy === 'name') {
                $query->orderBy('full_name', 'asc');
            } elseif ($this->sortBy === 'calls_count') {
                $query->withCount('calls')->orderBy('calls_count', 'desc');
            } else {
                $query->latest();
            }
            
            $clients = $query->paginate(10);
        }

        return view('sales::livewire.customer-tab', [
            'customers' => $clients,
            'statuses' => $statuses,
        ]);
    }
}
