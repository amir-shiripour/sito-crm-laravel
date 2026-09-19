<?php

declare(strict_types=1);

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Modules\Sales\App\Models\CampaignContact;
use Modules\Sales\App\Models\SalesDeal;
use Modules\Sales\App\Models\SalesPipeline;
use Modules\Clients\Entities\Client;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class CampaignLeadsTab extends Component
{
    use WithPagination;

    public ?int $selectedClientId = null;
    public string $search = '';
    public string $filterStatus = 'all'; // 'all', 'pending', 'contacted', 'responded', 'converted', 'lost'
    public string $filterSource = 'all'; // 'all', 'crm', 'campaign'
    public string $filterAgent = 'me';   // 'me', 'all', or numeric userId

    public function mount(?int $selectedClientId = null): void
    {
        $this->selectedClientId = $selectedClientId;
    }

    #[On('clientChanged')]
    public function handleClientChanged(?int $clientId): void
    {
        $this->selectedClientId = $clientId;
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => 'all'],
        'filterSource' => ['except' => 'all'],
        'filterAgent' => ['except' => 'me'],
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

    public function getIsManagerProperty(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->hasRole('super-admin')
            || $user->can('sales.manage')
            || $user->can('sales.leads.view.all');
    }

    public function getLeadStatsProperty(): array
    {
        $user = auth()->user();
        if (!$user) {
            return [
                'total' => 0,
                'pending' => 0,
                'contacted' => 0,
                'converted' => 0,
            ];
        }

        $isManager = $this->isManager;
        $targetUserId = null;
        if ($isManager) {
            if ($this->filterAgent === 'me') {
                $targetUserId = $user->id;
            } elseif ($this->filterAgent === 'all') {
                $targetUserId = null;
            } else {
                $targetUserId = (int) $this->filterAgent;
            }
        } else {
            $targetUserId = $user->id;
        }

        // Campaign Contacts counts
        $campQuery = CampaignContact::query();
        if ($targetUserId) {
            $targetUser = User::find($targetUserId);
            $targetRoles = $targetUser ? $targetUser->getRoleNames()->toArray() : [];
            $campQuery->where(function($q) use ($targetUserId, $targetRoles) {
                $q->where('assigned_to', $targetUserId)
                  ->orWhere(function($sub) use ($targetRoles) {
                      $sub->whereNull('assigned_to')
                          ->whereIn('assigned_role', $targetRoles);
                  });
            });
        }
        $campCounts = $campQuery->selectRaw("
            count(*) as total,
            sum(case when status = 'pending' or status is null then 1 else 0 end) as pending,
            sum(case when status in ('contacted', 'responded') then 1 else 0 end) as contacted,
            sum(case when status = 'converted' then 1 else 0 end) as converted
        ")->first();

        // Clients counts (direct assignment)
        $clientQuery = Client::query();
        if ($targetUserId) {
            $clientQuery->where(function($q) use ($targetUserId) {
                $q->whereHas('users', function($sub) use ($targetUserId) {
                    $sub->where('users.id', $targetUserId);
                })
                ->orWhere('created_by', $targetUserId);
            });
        } else {
            $clientQuery->where(function($q) {
                $q->whereHas('users')
                  ->orWhereNotNull('created_by');
            });
        }
        $linkedClientIds = CampaignContact::whereNotNull('client_id')->pluck('client_id')->filter()->toArray();
        if (!empty($linkedClientIds)) {
            $clientQuery->whereNotIn('id', $linkedClientIds);
        }

        $directClients = $clientQuery->get(['id', 'meta']);
        $directTotal = $directClients->count();
        $directClientIds = $directClients->pluck('id')->toArray();
        $dealClientIds = empty($directClientIds) ? [] : SalesDeal::whereIn('client_id', $directClientIds)
            ->whereIn('status', ['open', 'won'])
            ->pluck('client_id')
            ->flip()
            ->toArray();

        $directConverted = 0;
        $directContacted = 0;
        $directPending = 0;

        foreach ($directClients as $cl) {
            if (isset($dealClientIds[$cl->id])) {
                $directConverted++;
            } else {
                $st = $cl->meta['sales_lead_status'] ?? 'pending';
                if ($st === 'converted') {
                    $directConverted++;
                } elseif (in_array($st, ['contacted', 'responded'], true)) {
                    $directContacted++;
                } else {
                    $directPending++;
                }
            }
        }

        return [
            'total' => (int) ($campCounts->total ?? 0) + $directTotal,
            'pending' => (int) ($campCounts->pending ?? 0) + $directPending,
            'contacted' => (int) ($campCounts->contacted ?? 0) + $directContacted,
            'converted' => (int) ($campCounts->converted ?? 0) + $directConverted,
        ];
    }

    private function claimContactIfNeeded(CampaignContact $contact): void
    {
        if ($contact->assigned_to === null) {
            $contact->update(['assigned_to' => auth()->id()]);
            $this->dispatch('refreshStats');
        }
        $contact->ensureClientCreated(auth()->id() ? (int) auth()->id() : null);
    }

    public function claimContact(int $contactId): void
    {
        $contact = CampaignContact::findOrFail($contactId);
        $this->claimContactIfNeeded($contact);
        $this->dispatch('notify', message: 'لید با موفقیت به شما تخصیص یافت.', type: 'success');
    }

    public function updateLeadStatus(string $type, int $id, string $status): void
    {
        $allowedStatuses = ['pending', 'contacted', 'responded', 'converted', 'lost'];
        if (!in_array($status, $allowedStatuses, true)) {
            return;
        }

        if ($type === 'campaign') {
            $contact = CampaignContact::findOrFail($id);
            $this->claimContactIfNeeded($contact);
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

        if ($type === 'campaign') {
            $contact = CampaignContact::findOrFail($id);
            $this->claimContactIfNeeded($contact);

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

    public function initiateVoipCall(string $phone, ?int $clientId = null, ?int $contactId = null): void
    {
        if ($contactId) {
            $contact = CampaignContact::find($contactId);
            if ($contact) {
                $this->claimContactIfNeeded($contact);
                if (!$clientId && $contact->client_id) {
                    $clientId = $contact->client_id;
                }
            }
        }

        if ($clientId) {
            $this->dispatch('clientSelected', clientId: $clientId);
        }

        $this->dispatch('initiateVoip', phone: $phone);
        $this->dispatch('notify', message: 'در حال برقراری تماس...', type: 'info');
    }

    public function selectClient(int $clientId): void
    {
        $this->selectedClientId = $clientId;
        $this->dispatch('clientSelected', clientId: $clientId, openDrawer: true);
        $this->dispatch('openDrawer');
    }

    public function openLeadDrawer(string $type, int $id): void
    {
        $clientId = null;
        if ($type === 'crm') {
            $clientId = $id;
        } else {
            $contact = CampaignContact::findOrFail($id);
            $this->claimContactIfNeeded($contact);
            $client = $contact->ensureClientCreated(auth()->id() ? (int) auth()->id() : null);
            $clientId = $client ? $client->id : $contact->client_id;
        }

        if ($clientId) {
            $this->selectedClientId = $clientId;
            $this->dispatch('clientSelected', clientId: $clientId, openDrawer: true);
            $this->dispatch('openDrawer');
            $this->dispatch('notify', message: 'پرونده ۳۶۰ درجه لید در کشو باز شد.', type: 'info');
        }
    }

    public function activateContactInCockpit(string $type, int $id): void
    {
        $clientId = null;
        if ($type === 'crm') {
            $clientId = $id;
        } else {
            $contact = CampaignContact::findOrFail($id);
            $this->claimContactIfNeeded($contact);
            $client = $contact->ensureClientCreated(auth()->id() ? (int) auth()->id() : null);
            $clientId = $client ? $client->id : $contact->client_id;
        }

        if ($clientId) {
            $this->selectedClientId = $clientId;
            $this->dispatch('clientSelected', clientId: $clientId);
            $this->dispatch('notify', message: 'مشتری با موفقیت در میز کار فعال شد.', type: 'success');
        }
    }

    public function render()
    {
        $user = auth()->user();
        if (!$user) {
            return view('sales::livewire.campaign-leads-tab', [
                'leads' => new LengthAwarePaginator([], 0, 10),
                'salesAgents' => collect(),
                'isManager' => false,
                'leadStats' => [
                    'total' => 0,
                    'pending' => 0,
                    'contacted' => 0,
                    'converted' => 0,
                ],
                'selectedClientId' => null,
            ]);
        }

        $isManager = $this->isManager;
        $targetUserId = null;
        if ($isManager) {
            if ($this->filterAgent === 'me') {
                $targetUserId = $user->id;
            } elseif ($this->filterAgent === 'all') {
                $targetUserId = null;
            } else {
                $targetUserId = (int) $this->filterAgent;
            }
        } else {
            $targetUserId = $user->id;
        }

        $items = collect();

        // 1. Campaign Contacts
        if ($this->filterSource === 'all' || $this->filterSource === 'campaign') {
            $campQuery = CampaignContact::query()->with(['campaign', 'assignee']);

            if ($targetUserId) {
                $targetUser = User::find($targetUserId);
                $targetRoles = $targetUser ? $targetUser->getRoleNames()->toArray() : [];
                $campQuery->where(function($q) use ($targetUserId, $targetRoles) {
                    $q->where('assigned_to', $targetUserId)
                      ->orWhere(function($sub) use ($targetRoles) {
                          $sub->whereNull('assigned_to')
                              ->whereIn('assigned_role', $targetRoles);
                      });
                });
            }

            if (!empty($this->search)) {
                $campQuery->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            }

            $campContacts = $campQuery->orderBy('id', 'desc')->get();
            foreach ($campContacts as $cc) {
                $status = $cc->status ?? 'pending';
                $isConverted = ($status === 'converted');

                if ($this->filterStatus !== 'all') {
                    if ($status !== $this->filterStatus) {
                        continue;
                    }
                } else {
                    if ($isConverted) {
                        continue;
                    }
                }

                $items->push((object)[
                    'key' => 'camp_' . $cc->id,
                    'type' => 'campaign',
                    'id' => $cc->id,
                    'client_id' => $cc->client_id,
                    'name' => $cc->name,
                    'username' => null,
                    'phone' => $cc->phone,
                    'source_label' => $cc->campaign?->name ?? 'کمپین فروش',
                    'source_type' => 'campaign',
                    'status' => $status,
                    'is_converted' => $isConverted,
                    'date' => $cc->added_at ?? $cc->created_at,
                    'assigned_to' => $cc->assigned_to,
                    'assignee_name' => $cc->assignee?->name ?? 'تخصیص نیافته',
                ]);
            }
        }

        // 2. CRM Client Leads (assigned directly to agent)
        if ($this->filterSource === 'all' || $this->filterSource === 'crm') {
            $clientQuery = Client::query()->with(['status', 'users']);

            if ($targetUserId) {
                $clientQuery->where(function($q) use ($targetUserId) {
                    $q->whereHas('users', function($sub) use ($targetUserId) {
                        $sub->where('users.id', $targetUserId);
                    })
                    ->orWhere('created_by', $targetUserId);
                });
            } else {
                $clientQuery->where(function($q) {
                    $q->whereHas('users')
                      ->orWhereNotNull('created_by');
                });
            }

            // Exclude clients that are already represented as a CampaignContact to avoid duplicates
            $linkedClientIds = CampaignContact::whereNotNull('client_id')->pluck('client_id')->filter()->toArray();
            if (!empty($linkedClientIds)) {
                $clientQuery->whereNotIn('id', $linkedClientIds);
            }

            if (!empty($this->search)) {
                $clientQuery->where(function($q) {
                    $q->where('full_name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('username', 'like', '%' . $this->search . '%');
                });
            }

            $clients = $clientQuery->orderBy('id', 'desc')->get();

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

                    $isConverted = ($status === 'converted');

                    if ($this->filterStatus !== 'all') {
                        if ($status !== $this->filterStatus) {
                            continue;
                        }
                    } else {
                        if ($isConverted) {
                            continue;
                        }
                    }

                    $items->push((object)[
                        'key' => 'client_' . $cl->id,
                        'type' => 'crm',
                        'id' => $cl->id,
                        'client_id' => $cl->id,
                        'name' => $cl->full_name,
                        'username' => $cl->username,
                        'phone' => $cl->phone,
                        'source_label' => 'مشتریان مستقیم',
                        'source_type' => 'crm',
                        'client_status_label' => $cl->status?->label ?? $cl->status?->name,
                        'status' => $status,
                        'is_converted' => $isConverted,
                        'date' => $cl->created_at,
                        'assigned_to' => $cl->users->first()?->id ?? $cl->created_by,
                        'assignee_name' => $cl->users->isNotEmpty() ? $cl->users->pluck('name')->join(', ') : 'تخصیص نیافته',
                    ]);
                }
            }
        }

        // Sort items by date descending
        $sortedItems = $items->sortByDesc(function($item) {
            return $item->date ? $item->date->timestamp : 0;
        })->values();

        // Paginate
        $perPage = 10;
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

        $salesAgents = $isManager ? User::orderBy('name')->get(['id', 'name']) : collect();

        return view('sales::livewire.campaign-leads-tab', [
            'leads' => $leads,
            'isManager' => $isManager,
            'salesAgents' => $salesAgents,
            'leadStats' => $this->leadStats,
            'selectedClientId' => $this->selectedClientId,
        ]);
    }
}
