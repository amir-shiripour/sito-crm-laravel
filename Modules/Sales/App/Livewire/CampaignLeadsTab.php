<?php

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Sales\App\Models\CampaignContact;
use Modules\Clients\Entities\Client;

class CampaignLeadsTab extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = 'all'; // 'all', 'pending', 'contacted', 'responded', 'lost'

    protected $queryString = ['search', 'filterStatus'];

    private function getContactQuery()
    {
        $userRoles = [];
        if (auth()->check()) {
            $userRoles = auth()->user()->getRoleNames()->toArray();
        }

        return CampaignContact::query()
            ->where(function($q) use ($userRoles) {
                $q->where('assigned_to', auth()->id())
                  ->orWhere(function($sub) use ($userRoles) {
                      $sub->whereNull('assigned_to')
                          ->whereIn('assigned_role', $userRoles);
                  });
            });
    }
    private function claimContactIfNeeded(CampaignContact $contact)
    {
        if ($contact->assigned_to === null) {
            $contact->update(['assigned_to' => auth()->id()]);
            $this->dispatch('refreshStats');
        }
        $contact->ensureClientCreated(auth()->id() ? (int) auth()->id() : null);
    }

    public function claimContact(int $contactId)
    {
        $contact = $this->getContactQuery()->findOrFail($contactId);
        $this->claimContactIfNeeded($contact);
        $this->dispatch('notify', message: 'سرنخ با موفقیت به شما تخصیص یافت.', type: 'success');
    }

    public function updateContactStatus(int $contactId, string $status)
    {
        $contact = $this->getContactQuery()->findOrFail($contactId);
        $this->claimContactIfNeeded($contact);
        
        $allowedStatuses = ['pending', 'contacted', 'responded', 'converted', 'lost'];
        if (in_array($status, $allowedStatuses)) {
            $contact->update(['status' => $status]);
            $this->dispatch('refreshStats');
            $this->dispatch('notify', message: 'وضعیت سرنخ به‌روزرسانی شد.', type: 'success');
        }
    }

    public function convertToDeal(int $contactId)
    {
        $contact = $this->getContactQuery()->findOrFail($contactId);
        $this->claimContactIfNeeded($contact);

        if ($contact->status === 'converted') {
            $this->dispatch('notify', message: 'این سرنخ قبلاً به پرونده فروش تبدیل شده است.', type: 'warning');
            return;
        }

        $userId = auth()->id() ? (int) auth()->id() : null;
        $client = $contact->ensureClientCreated($userId);
        $clientId = $client ? $client->id : null;

        $firstStage = \Modules\Sales\App\Models\SalesPipeline::orderBy('order')->first();
        if (!$firstStage) {
            $firstStage = \Modules\Sales\App\Models\SalesPipeline::create([
                'name' => 'ارتباط اولیه', 
                'color' => '#3b82f6', 
                'order' => 1
            ]);
        }

        \Modules\Sales\App\Models\SalesDeal::create([
            'title' => 'پرونده: ' . $contact->name,
            'client_id' => $clientId,
            'pipeline_stage_id' => $firstStage->id,
            'user_id' => $userId,
            'expected_revenue' => 0.0,
            'probability' => 10,
            'status' => 'open',
            'stage_entered_at' => now(),
            'lead_source' => 'campaign',
            'created_by' => auth()->id(),
        ]);

        $contact->update(['status' => 'converted']);
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'مخاطب با موفقیت به پرونده فروش تبدیل شد.', type: 'success');
    }
    public function initiateVoipCall($phone, $contactId)
    {
        $contact = $this->getContactQuery()->findOrFail($contactId);
        $this->claimContactIfNeeded($contact);
        
        $this->dispatch('initiateVoip', phone: $phone);
        $this->dispatch('notify', message: 'در حال برقراری تماس...', type: 'info');
    }

    public function render()
    {
        $query = $this->getContactQuery()->with('campaign');

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        } else {
            $query->where('status', '!=', 'converted');
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('phone', 'like', '%'.$this->search.'%');
            });
        }

        $contacts = $query->orderBy('id', 'desc')->paginate(10);

        return view('sales::livewire.campaign-leads-tab', [
            'contacts' => $contacts
        ]);
    }
}
