<?php

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Modules\Sales\App\Models\SalesPipeline;
use Modules\Sales\App\Models\SalesDeal;
use Modules\Clients\Entities\Client;
use App\Models\User;

class DealTab extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStage = 'all';
    public string $filterStatus = 'all'; // 'all', 'open', 'won', 'lost'
    public string $sortBy = 'latest'; // 'latest', 'name', 'revenue'
    
    public ?int $selectedDealId = null;
    public bool $showCreateModal = false;

    // Create/Edit Deal Form Fields
    public ?int $editingDealId = null;
    public string $newDealTitle = '';
    public ?int $newDealClientId = null;
    public ?int $newDealStageId = null;
    public float $newDealExpectedRevenue = 0.0;
    public ?int $newDealProbability = null;
    public ?string $newDealExpectedCloseDate = null;
    public ?string $newDealSource = '';
    public ?int $newDealUserId = null;
    public string $newDealDescription = '';

    // Client connection mode
    public string $clientMode = 'existing'; // 'existing', 'new'

    // Client registration fields (when clientMode is 'new')
    public string $client_full_name = '';
    public string $client_phone = '';
    public ?string $client_email = null;
    public ?string $client_username = null;
    public ?string $client_national_code = null;
    public ?string $client_notes = null;

    // Dropdowns
    public $clients = [];
    public $users = [];
    public $pipelines = [];

    protected $queryString = ['search', 'filterStage', 'filterStatus', 'sortBy'];

    public function mount($selectedDealId = null)
    {
        $this->selectedDealId = $selectedDealId;
        $this->newDealUserId = auth()->id();
        $this->loadDropdowns();
    }

    public function loadDropdowns()
    {
        if (class_exists(Client::class)) {
            $this->clients = Client::orderBy('full_name')->get(['id', 'full_name', 'phone']);
        }
        $this->users = User::orderBy('name')->get(['id', 'name']);
        $this->pipelines = SalesPipeline::orderBy('order')->get();
    }

    #[On('dealChanged')]
    public function updateSelectedDeal($dealId)
    {
        $this->selectedDealId = $dealId;
    }

    public function selectDeal($id)
    {
        $this->selectedDealId = $id;
        $this->dispatch('dealSelected', dealId: $id);
    }

    public function openCreateModal()
    {
        $this->resetErrorBag();
        $this->resetDealForm();
        
        $firstPipeline = SalesPipeline::orderBy('order')->first();
        $this->newDealStageId = $firstPipeline?->id;
        $this->newDealUserId = auth()->id();
        
        $this->showCreateModal = true;
    }

    public function resetDealForm()
    {
        $this->editingDealId = null;
        $this->newDealTitle = '';
        $this->newDealClientId = null;
        $this->newDealExpectedRevenue = 0.0;
        $this->newDealProbability = null;
        $this->newDealExpectedCloseDate = null;
        $this->newDealSource = '';
        $this->newDealDescription = '';
        
        $this->clientMode = 'existing';
        $this->client_full_name = '';
        $this->client_phone = '';
        $this->client_email = null;
        $this->client_username = null;
        $this->client_national_code = null;
        $this->client_notes = null;
    }

    public function saveDeal()
    {
        $dealRules = [
            'newDealTitle' => 'required|string|max:191',
            'newDealStageId' => 'required|exists:sales_pipelines,id',
            'newDealExpectedRevenue' => 'required|numeric|min:0',
            'newDealProbability' => 'nullable|integer|min:0|max:100',
            'newDealExpectedCloseDate' => 'nullable|date',
            'newDealUserId' => 'required|exists:users,id',
            'clientMode' => 'required|in:existing,new',
        ];

        if ($this->clientMode === 'existing') {
            $dealRules['newDealClientId'] = 'required|exists:clients,id';
        } else {
            $dealRules['client_full_name'] = 'required|string|max:255';
            $dealRules['client_phone'] = 'required|string|unique:clients,phone' . ($this->editingDealId ? ','.$this->newDealClientId : '');
            if ($this->client_email) {
                $dealRules['client_email'] = 'nullable|email|unique:clients,email' . ($this->editingDealId ? ','.$this->newDealClientId : '');
            }
            if ($this->client_username) {
                $dealRules['client_username'] = 'nullable|string|unique:clients,username' . ($this->editingDealId ? ','.$this->newDealClientId : '');
            }
            if ($this->client_national_code) {
                $dealRules['client_national_code'] = 'nullable|string|max:10';
            }
        }

        $this->validate($dealRules);

        // 1. Create client if in new client mode
        if ($this->clientMode === 'new') {
            $username = $this->client_username;
            if (empty($username)) {
                // Generate username based on strategy
                $strategy = \Modules\Clients\Entities\ClientSetting::getValue('username_strategy')
                    ?: config('clients.username.strategy', 'email_local');
                $prefix = \Modules\Clients\Entities\ClientSetting::getValue('username_prefix', 'clt');
                
                $existsInClients = fn(string $u) =>
                    \DB::table('clients')->where('username', $u)->exists();

                $candidate = null;
                switch ($strategy) {
                    case 'email':
                        $candidate = (string) $this->client_email;
                        break;
                    case 'national_code':
                        $candidate = (string) $this->client_national_code;
                        break;
                    case 'mobile':
                        $digits = preg_replace('/\D+/', '', (string) $this->client_phone);
                        $candidate = $digits ?: null;
                        break;
                    case 'name_increment':
                        $base = \Illuminate\Support\Str::slug((string) $this->client_full_name);
                        if (!$base || strlen($base) < 3) {
                            $base = \Illuminate\Support\Str::slug(
                                (string) \Illuminate\Support\Str::before((string)$this->client_email, '@')
                            ) ?: 'user';
                        }
                        $candidate = $base;
                        break;
                    case 'prefix_increment':
                        $last = \DB::table('clients')
                            ->where('username', 'like', "{$prefix}-%")
                            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(username, '-', -1) AS UNSIGNED)) as mx")
                            ->value('mx');
                        $next = (int)$last + 1;
                        $candidate = sprintf('%s-%04d', $prefix, $next);
                        break;
                    case 'email_local':
                    default:
                        $local = (string) \Illuminate\Support\Str::before((string)$this->client_email, '@');
                        $base  = \Illuminate\Support\Str::slug($local ?: (string)$this->client_full_name) ?: 'user';
                        $candidate = $base;
                        break;
                }

                if (!$candidate) {
                    $candidate = 'clt_' . ($this->client_phone ?: \Illuminate\Support\Str::random(10));
                }

                $candidate = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $candidate);
                if (empty($candidate)) {
                    $candidate = 'clt_' . \Illuminate\Support\Str::random(8);
                }

                if ($existsInClients($candidate) && $strategy !== 'email' && $strategy !== 'mobile') {
                    $baseCandidate = $candidate;
                    $i = 1;
                    while ($existsInClients($baseCandidate . '_' . $i)) {
                        $i++;
                    }
                    $candidate = $baseCandidate . '_' . $i;
                }
                $username = $candidate;
            }

            $defaultStatus = \Modules\Clients\Entities\ClientStatus::active()->orderBy('sort_order')->first()
                ?? \Modules\Clients\Entities\ClientStatus::first();
            $statusId = $defaultStatus ? $defaultStatus->id : null;

            // Meta for sales agent (using getOrCreateSalesAgentFieldId)
            $meta = [];
            $salesAgentFieldId = \Modules\Sales\App\Models\CampaignContact::getOrCreateSalesAgentFieldId();
            if ($this->newDealUserId && $salesAgentFieldId) {
                $meta[$salesAgentFieldId] = (string) $this->newDealUserId;
            }

            $client = \Modules\Clients\Entities\Client::create([
                'full_name' => $this->client_full_name,
                'phone' => $this->client_phone,
                'email' => $this->client_email,
                'username' => $username,
                'national_code' => $this->client_national_code,
                'status_id' => $statusId,
                'meta' => $meta,
                'notes' => $this->client_notes,
                'created_by' => auth()->id() ?? $this->newDealUserId,
            ]);

            if ($client && $this->newDealUserId) {
                $client->users()->sync([$this->newDealUserId]);
            }

            $this->newDealClientId = $client->id;
        }

        $stage = SalesPipeline::find($this->newDealStageId);
        $status = 'open';
        $actualRevenue = null;
        if ($stage?->is_won) {
            $status = 'won';
            $actualRevenue = $this->newDealExpectedRevenue;
        } elseif ($stage?->is_lost) {
            $status = 'lost';
        }

        $data = [
            'title' => $this->newDealTitle,
            'client_id' => $this->newDealClientId,
            'pipeline_stage_id' => $this->newDealStageId,
            'expected_revenue' => $this->newDealExpectedRevenue,
            'actual_revenue' => $actualRevenue,
            'probability' => $this->newDealProbability,
            'expected_close_date' => $this->newDealExpectedCloseDate,
            'user_id' => $this->newDealUserId,
            'lead_source' => $this->newDealSource,
            'description' => $this->newDealDescription,
            'status' => $status,
            'stage_entered_at' => now(),
        ];

        if ($this->editingDealId) {
            $deal = SalesDeal::findOrFail($this->editingDealId);
            $deal->update($data);
            $message = 'پرونده فروش با موفقیت ویرایش شد.';
        } else {
            $data['created_by'] = auth()->id();
            $deal = SalesDeal::create($data);
            $message = 'پرونده فروش جدید با موفقیت ایجاد شد.';
        }

        $this->showCreateModal = false;
        $this->resetDealForm();
        $this->loadDropdowns();
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: $message, type: 'success');
        
        $this->selectDeal($deal->id);
    }

    public function editDeal($id)
    {
        $this->resetErrorBag();
        $deal = SalesDeal::findOrFail($id);
        
        $this->editingDealId = $deal->id;
        $this->newDealTitle = $deal->title;
        $this->newDealClientId = $deal->client_id;
        $this->newDealStageId = $deal->pipeline_stage_id;
        $this->newDealExpectedRevenue = $deal->expected_revenue;
        $this->newDealProbability = $deal->probability;
        $this->newDealExpectedCloseDate = $deal->expected_close_date ? $deal->expected_close_date->format('Y-m-d') : null;
        $this->newDealSource = $deal->lead_source ?? '';
        $this->newDealUserId = $deal->user_id;
        $this->newDealDescription = $deal->description ?? '';
        
        $this->clientMode = 'existing';
        
        $this->showCreateModal = true;
    }

    public function deleteDeal($id)
    {
        $deal = SalesDeal::findOrFail($id);
        $deal->delete();
        
        if ($this->selectedDealId == $id) {
            $this->selectedDealId = null;
            $this->selectedClientId = null;
            $this->dispatch('dealChanged', dealId: null);
            $this->dispatch('clientChanged', clientId: null);
        }
        
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'پرونده با موفقیت حذف شد.', type: 'success');
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStage() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingSortBy() { $this->resetPage(); }

    public function render()
    {
        $query = SalesDeal::query()
            ->with(['client', 'stage', 'owner'])
            ->visibleForUser(auth()->user());

        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                  ->orWhereHas('client', function($sub) {
                      $sub->where('full_name', 'like', '%'.$this->search.'%')
                          ->orWhere('phone', 'like', '%'.$this->search.'%')
                          ->orWhere('case_number', 'like', '%'.$this->search.'%');
                  });
            });
        }

        if ($this->filterStage !== 'all') {
            $query->where('pipeline_stage_id', $this->filterStage);
        }

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->sortBy === 'revenue') {
            $query->orderBy('expected_revenue', 'desc');
        } elseif ($this->sortBy === 'name') {
            $query->orderBy('title', 'asc');
        } else {
            $query->latest();
        }

        $deals = $query->paginate(10);

        return view('sales::livewire.deal-tab', [
            'deals' => $deals
        ]);
    }
}
