<?php

declare(strict_types=1);

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Modules\Clients\Entities\Client;
use Modules\Clients\Entities\ClientStatus;
use Modules\Sales\App\Models\Campaign;
use Modules\Sales\App\Models\CampaignContact;
use Modules\Sales\App\Models\SalesDeal;
use Modules\Sales\App\Models\SalesPipeline;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CampaignLeadManager extends Component
{
    use WithPagination;

    // Filters
    public string $search = '';
    public string $filterStatus = 'all'; // 'all', 'pending', 'contacted', 'responded', 'converted', 'lost'
    public string $filterSource = 'all'; // 'all', 'campaign', 'direct'
    public string $filterAgent = 'all';  // 'all', 'unassigned', or specific user id
    public string $filterCampaign = 'all'; // 'all', or specific campaign id
    public string $sort = 'newest';      // 'newest', 'oldest', 'name_asc', 'name_desc'
    public bool $filterOpen = false;

    // Bulk selection & Actions
    public array $selectedKeys = [];
    public ?int $assignToUserId = null;
    public ?int $assignToCampaignId = null;
    public string $bulkStatus = '';

    // Create Lead Modal
    public bool $showCreateModal = false;
    public string $new_name = '';
    public string $new_phone = '';
    public string $new_email = '';
    public string $new_source_type = 'direct'; // 'direct' or 'campaign'
    public ?int $new_campaign_id = null;
    public ?int $new_assigned_to = null;
    public string $new_status = 'pending';
    public ?string $new_note = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => 'all'],
        'filterSource' => ['except' => 'all'],
        'filterAgent' => ['except' => 'all'],
        'filterCampaign' => ['except' => 'all'],
        'sort' => ['except' => 'newest'],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterSource(): void
    {
        $this->resetPage();
    }

    public function updatedFilterAgent(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCampaign(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterStatus', 'filterSource', 'filterAgent', 'filterCampaign', 'sort']);
        $this->resetPage();
        $this->dispatch('notify', message: 'فیلترها با موفقیت پاک‌سازی شدند.', type: 'info');
    }

    public function getActiveFiltersCountProperty(): int
    {
        $count = 0;
        if (!empty($this->search)) $count++;
        if ($this->filterStatus !== 'all') $count++;
        if ($this->filterSource !== 'all') $count++;
        if ($this->filterAgent !== 'all') $count++;
        if ($this->filterCampaign !== 'all') $count++;
        if ($this->sort !== 'newest') $count++;
        return $count;
    }

    #[Computed]
    public function leadStats(): array
    {
        // 1. Campaign Contacts Stats
        $campCounts = CampaignContact::query()->selectRaw("
            count(*) as total,
            sum(case when assigned_to is null then 1 else 0 end) as unassigned,
            sum(case when status in ('contacted', 'responded') then 1 else 0 end) as in_qualification,
            sum(case when status = 'converted' then 1 else 0 end) as converted
        ")->first();

        // 2. Direct Clients Stats
        $clientQuery = Client::query();
        $linkedClientIds = CampaignContact::whereNotNull('client_id')->pluck('client_id')->filter()->toArray();
        if (!empty($linkedClientIds)) {
            $clientQuery->whereNotIn('id', $linkedClientIds);
        }

        $directClients = $clientQuery->with('users')->get(['id', 'meta']);
        $directTotal = $directClients->count();
        $directUnassigned = 0;
        $directInQualification = 0;
        $directConverted = 0;

        $directClientIds = $directClients->pluck('id')->toArray();
        $dealClientIds = empty($directClientIds) ? [] : SalesDeal::whereIn('client_id', $directClientIds)
            ->whereIn('status', ['open', 'won'])
            ->pluck('client_id')
            ->flip()
            ->toArray();

        foreach ($directClients as $cl) {
            if ($cl->users->isEmpty()) {
                $directUnassigned++;
            }

            if (isset($dealClientIds[$cl->id])) {
                $directConverted++;
            } else {
                $st = $cl->meta['sales_lead_status'] ?? 'pending';
                if ($st === 'converted') {
                    $directConverted++;
                } elseif (in_array($st, ['contacted', 'responded'], true)) {
                    $directInQualification++;
                }
            }
        }

        $campaignsCount = Campaign::where('status', 'active')->count();

        return [
            'total' => (int) ($campCounts->total ?? 0) + $directTotal,
            'unassigned' => (int) ($campCounts->unassigned ?? 0) + $directUnassigned,
            'in_qualification' => (int) ($campCounts->in_qualification ?? 0) + $directInQualification,
            'converted' => (int) ($campCounts->converted ?? 0) + $directConverted,
            'campaigns_count' => $campaignsCount,
        ];
    }

    public function toggleSelectAll(array $pageKeys): void
    {
        $allSelected = count(array_intersect($pageKeys, $this->selectedKeys)) === count($pageKeys);
        if ($allSelected) {
            $this->selectedKeys = array_values(array_diff($this->selectedKeys, $pageKeys));
        } else {
            $this->selectedKeys = array_values(array_unique(array_merge($this->selectedKeys, $pageKeys)));
        }
    }

    public function bulkAssign(): void
    {
        if (empty($this->selectedKeys)) {
            $this->dispatch('notify', message: 'هیچ لیدی انتخاب نشده است.', type: 'warning');
            return;
        }

        if (!$this->assignToUserId) {
            $this->dispatch('notify', message: 'لطفاً کارشناس مورد نظر را انتخاب کنید.', type: 'warning');
            return;
        }

        $user = User::find($this->assignToUserId);
        if (!$user) return;

        $salesAgentFieldId = CampaignContact::getOrCreateSalesAgentFieldId();

        foreach ($this->selectedKeys as $key) {
            [$type, $id] = explode('_', (string) $key);
            $id = (int) $id;

            if ($type === 'camp') {
                $contact = CampaignContact::find($id);
                if ($contact) {
                    $contact->update(['assigned_to' => $this->assignToUserId]);
                    $client = $contact->ensureClientCreated($this->assignToUserId);
                    if ($client) {
                        $client->users()->sync([$this->assignToUserId]);
                    }
                }
            } elseif ($type === 'client') {
                $client = Client::find($id);
                if ($client) {
                    $client->users()->sync([$this->assignToUserId]);
                    $meta = $client->meta ?? [];
                    if ($salesAgentFieldId) {
                        $meta[$salesAgentFieldId] = (string) $this->assignToUserId;
                    }
                    if (!isset($meta['sales_lead_status'])) {
                        $meta['sales_lead_status'] = 'pending';
                    }
                    $client->meta = $meta;
                    $client->save();

                    CampaignContact::where('client_id', $id)->update(['assigned_to' => $this->assignToUserId]);
                }
            }
        }

        $this->selectedKeys = [];
        $this->assignToUserId = null;
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'لیدهای انتخاب‌شده با موفقیت به کارشناس تخصیص یافتند.', type: 'success');
    }

    public function bulkAddToCampaign(): void
    {
        if (empty($this->selectedKeys)) {
            $this->dispatch('notify', message: 'هیچ لیدی انتخاب نشده است.', type: 'warning');
            return;
        }

        if (!$this->assignToCampaignId) {
            $this->dispatch('notify', message: 'لطفاً کمپین مورد نظر را انتخاب کنید.', type: 'warning');
            return;
        }

        $campaign = Campaign::find($this->assignToCampaignId);
        if (!$campaign) return;

        $addedCount = 0;

        foreach ($this->selectedKeys as $key) {
            [$type, $id] = explode('_', (string) $key);
            $id = (int) $id;

            if ($type === 'client') {
                $client = Client::find($id);
                if ($client) {
                    $exists = CampaignContact::where('campaign_id', $this->assignToCampaignId)
                        ->where('client_id', $client->id)
                        ->exists();

                    if (!$exists) {
                        CampaignContact::create([
                            'campaign_id' => $this->assignToCampaignId,
                            'client_id' => $client->id,
                            'name' => $client->full_name,
                            'phone' => $client->phone,
                            'email' => $client->email,
                            'status' => 'pending',
                            'source' => 'direct_lead',
                            'added_at' => now(),
                        ]);
                        $addedCount++;
                    }
                }
            } elseif ($type === 'camp') {
                $contact = CampaignContact::find($id);
                if ($contact && $contact->campaign_id !== $this->assignToCampaignId) {
                    $exists = CampaignContact::where('campaign_id', $this->assignToCampaignId)
                        ->where('phone', $contact->phone)
                        ->exists();

                    if (!$exists) {
                        CampaignContact::create([
                            'campaign_id' => $this->assignToCampaignId,
                            'client_id' => $contact->client_id,
                            'name' => $contact->name,
                            'phone' => $contact->phone,
                            'email' => $contact->email,
                            'status' => 'pending',
                            'source' => 'campaign_transfer',
                            'added_at' => now(),
                        ]);
                        $addedCount++;
                    }
                }
            }
        }

        $this->selectedKeys = [];
        $this->assignToCampaignId = null;
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: "تعداد {$addedCount} لید با موفقیت به کمپین اضافه شدند.", type: 'success');
    }

    public function bulkUpdateStatus(): void
    {
        if (empty($this->selectedKeys)) {
            $this->dispatch('notify', message: 'هیچ لیدی انتخاب نشده است.', type: 'warning');
            return;
        }

        if (empty($this->bulkStatus)) {
            $this->dispatch('notify', message: 'لطفاً وضعیت مورد نظر را انتخاب کنید.', type: 'warning');
            return;
        }

        foreach ($this->selectedKeys as $key) {
            [$type, $id] = explode('_', (string) $key);
            $id = (int) $id;

            if ($type === 'camp') {
                $contact = CampaignContact::find($id);
                if ($contact) {
                    $contact->update(['status' => $this->bulkStatus]);
                }
            } elseif ($type === 'client') {
                $client = Client::find($id);
                if ($client) {
                    $meta = $client->meta ?? [];
                    $meta['sales_lead_status'] = $this->bulkStatus;
                    $client->meta = $meta;
                    $client->save();
                }
            }
        }

        $this->selectedKeys = [];
        $this->bulkStatus = '';
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'وضعیت لیدهای انتخاب‌شده به‌روزرسانی شد.', type: 'success');
    }

    public function bulkConvertToDeals(): void
    {
        if (empty($this->selectedKeys)) {
            $this->dispatch('notify', message: 'هیچ لیدی انتخاب نشده است.', type: 'warning');
            return;
        }

        $userId = auth()->id() ? (int) auth()->id() : null;
        $firstStage = SalesPipeline::orderBy('order')->first();
        if (!$firstStage) {
            $firstStage = SalesPipeline::create([
                'name' => 'ارتباط اولیه',
                'color' => '#3b82f6',
                'order' => 1,
            ]);
        }

        $convertedCount = 0;

        foreach ($this->selectedKeys as $key) {
            [$type, $id] = explode('_', (string) $key);
            $id = (int) $id;

            if ($type === 'camp') {
                $contact = CampaignContact::find($id);
                if ($contact && $contact->status !== 'converted') {
                    $client = $contact->ensureClientCreated($userId);
                    $clientId = $client ? $client->id : null;
                    $contact->update(['status' => 'converted']);

                    SalesDeal::create([
                        'title' => $contact->name,
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
                    $convertedCount++;
                }
            } elseif ($type === 'client') {
                $client = Client::find($id);
                if ($client) {
                    $dealExists = SalesDeal::where('client_id', $client->id)
                        ->whereIn('status', ['open', 'won'])
                        ->exists();

                    if (!$dealExists) {
                        $meta = $client->meta ?? [];
                        $meta['sales_lead_status'] = 'converted';
                        $client->meta = $meta;
                        $client->save();

                        SalesDeal::create([
                            'title' => $client->full_name,
                            'client_id' => $client->id,
                            'pipeline_stage_id' => $firstStage->id,
                            'user_id' => $userId,
                            'expected_revenue' => 0.0,
                            'probability' => 10,
                            'status' => 'open',
                            'stage_entered_at' => now(),
                            'lead_source' => 'direct_lead',
                            'created_by' => auth()->id(),
                        ]);
                        $convertedCount++;
                    }
                }
            }
        }

        $this->selectedKeys = [];
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: "تعداد {$convertedCount} لید با موفقیت به پرونده فروش تبدیل شدند.", type: 'success');
    }

    public function bulkDelete(): void
    {
        if (empty($this->selectedKeys)) {
            $this->dispatch('notify', message: 'هیچ لیدی انتخاب نشده است.', type: 'warning');
            return;
        }

        $count = 0;
        foreach ($this->selectedKeys as $key) {
            [$type, $id] = explode('_', (string) $key);
            $id = (int) $id;

            if ($type === 'camp') {
                CampaignContact::where('id', $id)->delete();
                $count++;
            } elseif ($type === 'client') {
                // Delete prospect client if authorized
                Client::where('id', $id)->delete();
                $count++;
            }
        }

        $this->selectedKeys = [];
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: "تعداد {$count} لید حذف شد.", type: 'success');
    }

    public function updateLeadStatus(string $type, int $id, string $status): void
    {
        $allowedStatuses = ['pending', 'contacted', 'responded', 'converted', 'lost'];
        if (!in_array($status, $allowedStatuses, true)) return;

        if ($type === 'camp') {
            $contact = CampaignContact::findOrFail($id);
            $contact->update(['status' => $status]);
        } else {
            $client = Client::findOrFail($id);
            $meta = $client->meta ?? [];
            $meta['sales_lead_status'] = $status;
            $client->meta = $meta;
            $client->save();
        }

        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'وضعیت لید به‌روزرسانی شد.', type: 'success');
    }

    public function convertToDeal(string $type, int $id): void
    {
        $userId = auth()->id() ? (int) auth()->id() : null;

        if ($type === 'camp') {
            $contact = CampaignContact::findOrFail($id);
            if ($contact->status === 'converted') {
                $this->dispatch('notify', message: 'این لید قبلاً به پرونده فروش تبدیل شده است.', type: 'warning');
                return;
            }

            $client = $contact->ensureClientCreated($userId);
            $clientId = $client ? $client->id : null;
            $title = $contact->name;
            $leadSource = 'campaign';
            $contact->update(['status' => 'converted']);
        } else {
            $client = Client::findOrFail($id);
            $dealExists = SalesDeal::where('client_id', $client->id)
                ->whereIn('status', ['open', 'won'])
                ->exists();

            if ($dealExists) {
                $this->dispatch('notify', message: 'این لید قبلاً به پرونده فروش تبدیل شده است.', type: 'warning');
                return;
            }

            $clientId = $client->id;
            $title = $client->full_name;
            $leadSource = 'direct_lead';

            $meta = $client->meta ?? [];
            $meta['sales_lead_status'] = 'converted';
            $client->meta = $meta;
            $client->save();
        }

        $firstStage = SalesPipeline::orderBy('order')->first();
        if (!$firstStage) {
            $firstStage = SalesPipeline::create([
                'name' => 'ارتباط اولیه',
                'color' => '#3b82f6',
                'order' => 1,
            ]);
        }

        SalesDeal::create([
            'title' => $title,
            'client_id' => $clientId,
            'pipeline_stage_id' => $firstStage->id,
            'user_id' => $userId,
            'expected_revenue' => 0.0,
            'probability' => 10,
            'status' => 'open',
            'stage_entered_at' => now(),
            'lead_source' => $leadSource,
            'created_by' => auth()->id(),
        ]);

        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'لید با موفقیت به پرونده فروش تبدیل شد.', type: 'success');
    }

    public function assignSingleAgent(string $type, int $id, int $userId): void
    {
        $salesAgentFieldId = CampaignContact::getOrCreateSalesAgentFieldId();

        if ($type === 'camp') {
            $contact = CampaignContact::findOrFail($id);
            $contact->update(['assigned_to' => $userId]);
            $client = $contact->ensureClientCreated($userId);
            if ($client) {
                $client->users()->sync([$userId]);
            }
        } else {
            $client = Client::findOrFail($id);
            $client->users()->sync([$userId]);
            $meta = $client->meta ?? [];
            if ($salesAgentFieldId) {
                $meta[$salesAgentFieldId] = (string) $userId;
            }
            $client->meta = $meta;
            $client->save();
        }

        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'کارشناس مسئول با موفقیت به‌روزرسانی شد.', type: 'success');
    }

    public function initiateVoipCall(string $phone, ?int $clientId = null, ?int $contactId = null): void
    {
        if ($contactId) {
            $contact = CampaignContact::find($contactId);
            if ($contact && !$clientId && $contact->client_id) {
                $clientId = $contact->client_id;
            }
        }

        if ($clientId) {
            $this->dispatch('clientSelected', clientId: $clientId);
        }

        $this->dispatch('initiateVoip', phone: $phone);
        $this->dispatch('notify', message: 'در حال برقراری تماس صوتی...', type: 'info');
    }

    public function deleteLead(string $type, int $id): void
    {
        if ($type === 'camp') {
            CampaignContact::findOrFail($id)->delete();
        } else {
            Client::findOrFail($id)->delete();
        }

        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'لید با موفقیت حذف شد.', type: 'success');
    }

    public function openCreateModal(): void
    {
        $this->reset(['new_name', 'new_phone', 'new_email', 'new_source_type', 'new_campaign_id', 'new_assigned_to', 'new_note']);
        $this->new_status = 'pending';
        $this->showCreateModal = true;
    }

    public function saveNewLead(): void
    {
        $this->validate([
            'new_name' => 'required|string|max:255',
            'new_phone' => 'nullable|string|max:50',
            'new_email' => 'nullable|email|max:255',
            'new_source_type' => 'required|in:direct,campaign',
            'new_campaign_id' => 'nullable|required_if:new_source_type,campaign|exists:sales_campaigns,id',
            'new_assigned_to' => 'nullable|exists:users,id',
            'new_status' => 'required|in:pending,contacted,responded,converted,lost',
            'new_note' => 'nullable|string|max:500',
        ]);

        if ($this->new_source_type === 'campaign' && $this->new_campaign_id) {
            CampaignContact::create([
                'campaign_id' => $this->new_campaign_id,
                'name' => $this->new_name,
                'phone' => $this->new_phone ?: null,
                'email' => $this->new_email ?: null,
                'assigned_to' => $this->new_assigned_to,
                'status' => $this->new_status,
                'source' => 'manual_entry',
                'added_at' => now(),
            ]);
        } else {
            // Create Direct Client Lead
            $prospectStatus = ClientStatus::whereIn('key', ['prospect', 'lead', 'prospects', 'leads'])->first();
            $meta = [
                'sales_lead_status' => $this->new_status,
                'lead_note' => $this->new_note,
            ];

            $salesAgentFieldId = CampaignContact::getOrCreateSalesAgentFieldId();
            if ($this->new_assigned_to && $salesAgentFieldId) {
                $meta[$salesAgentFieldId] = (string) $this->new_assigned_to;
            }

            $client = Client::create([
                'full_name' => $this->new_name,
                'phone' => $this->new_phone ?: null,
                'email' => $this->new_email ?: null,
                'status_id' => $prospectStatus?->id,
                'created_by' => auth()->id(),
                'meta' => $meta,
                'notes' => $this->new_note,
            ]);

            if ($this->new_assigned_to) {
                $client->users()->sync([$this->new_assigned_to]);
            }
        }

        $this->showCreateModal = false;
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'لید جدید با موفقیت ثبت شد.', type: 'success');
    }

    public function render()
    {
        $items = collect();

        // 1. Campaign Contacts
        if ($this->filterSource === 'all' || $this->filterSource === 'campaign') {
            $campQuery = CampaignContact::query()->with(['campaign', 'assignee']);

            if (!empty($this->search)) {
                $campQuery->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->filterStatus !== 'all') {
                $campQuery->where('status', $this->filterStatus);
            }

            if ($this->filterAgent === 'unassigned') {
                $campQuery->whereNull('assigned_to');
            } elseif ($this->filterAgent !== 'all' && is_numeric($this->filterAgent)) {
                $campQuery->where('assigned_to', (int) $this->filterAgent);
            }

            if ($this->filterCampaign !== 'all' && is_numeric($this->filterCampaign)) {
                $campQuery->where('campaign_id', (int) $this->filterCampaign);
            }

            $campContacts = $campQuery->get();
            foreach ($campContacts as $cc) {
                $status = $cc->status ?? 'pending';
                $items->push((object)[
                    'key' => 'camp_' . $cc->id,
                    'type' => 'camp',
                    'id' => $cc->id,
                    'client_id' => $cc->client_id,
                    'name' => $cc->name,
                    'username' => null,
                    'phone' => $cc->phone,
                    'email' => $cc->email,
                    'source_label' => $cc->campaign?->name ?? 'کمپین تبلیغاتی',
                    'source_type' => 'campaign',
                    'campaign_id' => $cc->campaign_id,
                    'status' => $status,
                    'is_converted' => ($status === 'converted'),
                    'date' => $cc->added_at ?? $cc->created_at,
                    'assigned_to' => $cc->assigned_to,
                    'assignee_name' => $cc->assignee?->name ?? 'تخصیص نیافته',
                ]);
            }
        }

        // 2. Direct Clients (Prospects / Leads)
        if ($this->filterSource === 'all' || $this->filterSource === 'direct') {
            $clientQuery = Client::query()->with(['status', 'users']);

            $linkedClientIds = CampaignContact::whereNotNull('client_id')->pluck('client_id')->filter()->toArray();
            if (!empty($linkedClientIds)) {
                $clientQuery->whereNotIn('id', $linkedClientIds);
            }

            if (!empty($this->search)) {
                $clientQuery->where(function($q) {
                    $q->where('full_name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('username', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->filterAgent === 'unassigned') {
                $clientQuery->whereDoesntHave('users');
            } elseif ($this->filterAgent !== 'all' && is_numeric($this->filterAgent)) {
                $targetId = (int) $this->filterAgent;
                $clientQuery->where(function($q) use ($targetId) {
                    $q->whereHas('users', fn($sub) => $sub->where('users.id', $targetId))
                      ->orWhere('created_by', $targetId);
                });
            }

            // If campaign filter is active, direct clients won't match unless 'all'
            if ($this->filterCampaign === 'all') {
                $clients = $clientQuery->get();

                if ($clients->isNotEmpty()) {
                    $clientIds = $clients->pluck('id')->toArray();
                    $existingDealClientIds = SalesDeal::whereIn('client_id', $clientIds)
                        ->whereIn('status', ['open', 'won'])
                        ->pluck('client_id')
                        ->flip()
                        ->toArray();

                    foreach ($clients as $cl) {
                        $meta = $cl->meta ?? [];
                        $hasDeal = isset($existingDealClientIds[$cl->id]);
                        $status = $meta['sales_lead_status'] ?? null;
                        if ($hasDeal) {
                            $status = 'converted';
                        } elseif (!$status) {
                            $status = 'pending';
                        }

                        if ($this->filterStatus !== 'all' && $status !== $this->filterStatus) {
                            continue;
                        }

                        $items->push((object)[
                            'key' => 'client_' . $cl->id,
                            'type' => 'client',
                            'id' => $cl->id,
                            'client_id' => $cl->id,
                            'name' => $cl->full_name,
                            'username' => $cl->username,
                            'phone' => $cl->phone,
                            'email' => $cl->email,
                            'source_label' => 'مشتریان مستقیم',
                            'source_type' => 'direct',
                            'campaign_id' => null,
                            'client_status_label' => $cl->status?->label ?? $cl->status?->name,
                            'status' => $status,
                            'is_converted' => ($status === 'converted'),
                            'date' => $cl->created_at,
                            'assigned_to' => $cl->users->first()?->id ?? $cl->created_by,
                            'assignee_name' => $cl->users->isNotEmpty() ? $cl->users->pluck('name')->join('، ') : 'تخصیص نیافته',
                        ]);
                    }
                }
            }
        }

        // Sorting
        $sortedItems = match($this->sort) {
            'oldest' => $items->sortBy(fn($i) => $i->date ? $i->date->timestamp : 0)->values(),
            'name_asc' => $items->sortBy('name')->values(),
            'name_desc' => $items->sortByDesc('name')->values(),
            default => $items->sortByDesc(fn($i) => $i->date ? $i->date->timestamp : 0)->values(),
        };

        // Pagination
        $perPage = 15;
        $page = $this->getPage();
        $total = $sortedItems->count();
        $sliced = $sortedItems->slice(($page - 1) * $perPage, $perPage)->values();

        $leads = new LengthAwarePaginator(
            $sliced,
            $total,
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        $pageKeys = $sliced->pluck('key')->toArray();

        $salesAgents = User::orderBy('name')->get(['id', 'name']);
        $campaigns = Campaign::orderBy('name')->get(['id', 'name', 'status']);

        return view('sales::livewire.campaign-lead-manager', [
            'leads' => $leads,
            'pageKeys' => $pageKeys,
            'salesAgents' => $salesAgents,
            'campaigns' => $campaigns,
            'leadStats' => $this->leadStats,
            'activeFiltersCount' => $this->activeFiltersCount,
        ]);
    }
}
