<?php

declare(strict_types=1);

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Clients\Entities\Client;
use Modules\Clients\Entities\ClientStatus;
use App\Models\User;

class CampaignLeadManager extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $selectedStatusId = null;
    public array $selectedClientIds = [];
    public ?int $assignToUserId = null;
    public ?int $assignToCampaignId = null;

    protected $queryString = ['search', 'selectedStatusId'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedStatusId()
    {
        $this->resetPage();
    }

    public function bulkAssign()
    {
        $this->validate([
            'assignToUserId' => 'required|exists:users,id',
            'selectedClientIds' => 'required|array|min:1',
        ]);

        $user = User::find($this->assignToUserId);
        if (!$user) return;

        foreach ($this->selectedClientIds as $clientId) {
            $client = Client::find($clientId);
            if ($client) {
                // Sync the client to this user (allocate)
                $client->users()->sync([$this->assignToUserId]);
            }
        }

        $this->selectedClientIds = [];
        $this->assignToUserId = null;
        $this->dispatch('notify', message: 'سرنخ‌های انتخاب‌شده با موفقیت تخصیص یافتند.', type: 'success');
    }

    public function addToCampaign()
    {
        $this->validate([
            'assignToCampaignId' => 'required|exists:sales_campaigns,id',
            'selectedClientIds' => 'required|array|min:1',
        ]);

        $campaign = \Modules\Sales\App\Models\Campaign::find($this->assignToCampaignId);
        if (!$campaign) return;

        foreach ($this->selectedClientIds as $clientId) {
            $client = Client::find($clientId);
            if ($client) {
                $exists = \Modules\Sales\App\Models\CampaignContact::where('campaign_id', $this->assignToCampaignId)
                    ->where('client_id', $clientId)
                    ->exists();

                if (!$exists) {
                    \Modules\Sales\App\Models\CampaignContact::create([
                        'campaign_id' => $this->assignToCampaignId,
                        'client_id' => $clientId,
                        'name' => $client->full_name,
                        'phone' => $client->phone,
                        'email' => $client->email,
                        'status' => 'pending',
                        'source' => 'crm_filter',
                    ]);
                }
            }
        }

        $this->selectedClientIds = [];
        $this->assignToCampaignId = null;
        $this->dispatch('notify', message: 'سرنخ‌های انتخاب‌شده با موفقیت به کمپین اضافه شدند.', type: 'success');
    }

    public function toggleSelectAll($clientIds)
    {
        if (count($this->selectedClientIds) === count($clientIds)) {
            $this->selectedClientIds = [];
        } else {
            $this->selectedClientIds = $clientIds;
        }
    }

    public function render()
    {
        $user = auth()->user();
        $query = Client::with(['status', 'users'])->visibleForUser($user);

        // Filter by search
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('username', 'like', '%' . $this->search . '%');
            });
        }

        // Filter by status
        if ($this->selectedStatusId) {
            $query->where('status_id', $this->selectedStatusId);
        } else {
            // Default to show only prospects/leads if no status is filtered
            // Let's check if there are statuses named 'prospect' or 'lead'
            $leadStatusIds = ClientStatus::whereIn('key', ['prospect', 'lead', 'prospects', 'leads'])
                ->pluck('id')
                ->toArray();
            if (!empty($leadStatusIds)) {
                $query->whereIn('status_id', $leadStatusIds);
            }
        }

        $leads = $query->latest()->paginate(10);
        $clientIdsOnPage = $leads->pluck('id')->toArray();

        $statuses = ClientStatus::active()->get();
        $salesAgents = User::orderBy('name')->get();
        $campaigns = \Modules\Sales\App\Models\Campaign::orderBy('name')->get();

        return view('sales::livewire.campaign-lead-manager', [
            'leads' => $leads,
            'statuses' => $statuses,
            'salesAgents' => $salesAgents,
            'campaigns' => $campaigns,
            'clientIdsOnPage' => $clientIdsOnPage,
        ]);
    }
}
