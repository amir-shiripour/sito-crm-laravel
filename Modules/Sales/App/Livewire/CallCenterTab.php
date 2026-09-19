<?php

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Modules\Sales\App\Models\Campaign;
use Modules\Sales\App\Models\SalesDeal;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Modules\ClientCalls\Entities\ClientCall;
use Morilog\Jalali\Jalalian;

class CallCenterTab extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterDate = 'all'; // 'today', 'week', 'month', 'all'
    public string $filterClientMode = 'active'; // 'active', 'all'
    public ?int $selectedClientId = null;
    public ?string $selectedClientName = null;
    public ?int $selectedDealId = null;
    public ?int $editingCallId = null;
    
    // Client search in modal
    public string $clientSearch = '';
    public array $clientSearchResults = [];

    // Form fields
    public ?int $campaign_id = null;
    public ?int $deal_id = null;
    public ?string $call_date_jalali = null;
    public ?string $call_time = null;
    public ?int $duration_seconds = null;
    public string $direction = 'outbound';
    public string $status = 'done';
    public ?string $reason = null;
    public ?string $result = null;
    public ?string $next_action = null;
    public ?string $next_action_date_jalali = null;
    public ?string $contact_phone = null;
    public ?string $notes = null;
    
    public bool $showCreateModal = false;

    public function mount($selectedClientId = null)
    {
        $this->selectedClientId = $selectedClientId ? (int) $selectedClientId : null;
        $this->loadSelectedClientData();
        $this->call_date_jalali = Jalalian::now()->format('Y/m/d');
        $this->call_time = now()->format('H:i');
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
        $this->selectedDealId = $dealId;
        $this->deal_id = $dealId;
    }

    public function updatedClientSearch($value)
    {
        $value = trim($value);
        if (mb_strlen($value) < 2) {
            $this->clientSearchResults = [];
            return;
        }

        if (class_exists(\Modules\Clients\Entities\Client::class)) {
            $user = auth()->user();
            $this->clientSearchResults = \Modules\Clients\Entities\Client::query()
                ->visibleForUser($user)
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

    public function selectClientForModal($id)
    {
        $this->selectedClientId = $id;
        $this->clientSearch = '';
        $this->clientSearchResults = [];
        $this->loadSelectedClientData();
        $this->autoSelectDealIfAvailable();
    }

    public function clearSelectedClientInModal()
    {
        $this->selectedClientId = null;
        $this->selectedClientName = null;
        $this->contact_phone = null;
        $this->deal_id = null;
        $this->clientSearch = '';
        $this->clientSearchResults = [];
    }

    protected function loadSelectedClientData()
    {
        if ($this->selectedClientId && class_exists(\Modules\Clients\Entities\Client::class)) {
            $client = \Modules\Clients\Entities\Client::find($this->selectedClientId);
            if ($client) {
                $this->selectedClientName = $client->full_name ?: $client->username;
                if (empty($this->contact_phone)) {
                    $this->contact_phone = $client->phone;
                }
            }
        }
    }

    protected function autoSelectDealIfAvailable()
    {
        if ($this->selectedClientId) {
            $openDeals = SalesDeal::where('client_id', $this->selectedClientId)
                ->where('status', 'open')
                ->latest()
                ->get();

            if ($openDeals->isNotEmpty()) {
                if ($this->selectedDealId && $openDeals->contains('id', $this->selectedDealId)) {
                    $this->deal_id = $this->selectedDealId;
                } elseif (!$this->deal_id || !$openDeals->contains('id', $this->deal_id)) {
                    $this->deal_id = $openDeals->first()->id;
                }
            } else {
                $this->deal_id = null;
            }
        } else {
            $this->deal_id = null;
        }
    }

    public function getAvailableDealsProperty()
    {
        if (!$this->selectedClientId) {
            return collect();
        }
        return SalesDeal::where('client_id', $this->selectedClientId)
            ->where('status', 'open')
            ->get();
    }

    #[On('openCreateCallModal')]
    public function openCreateModal()
    {
        if (!$this->checkCanCreate()) {
            $this->dispatch('notify', message: 'شما مجوز ثبت تماس ندارید.', type: 'error');
            return;
        }

        $this->resetErrorBag();
        $this->reset([
            'campaign_id', 'deal_id', 'duration_seconds', 'reason', 'result', 
            'next_action', 'next_action_date_jalali', 'notes', 'editingCallId',
            'clientSearch', 'clientSearchResults'
        ]);
        
        $this->call_date_jalali = Jalalian::now()->format('Y/m/d');
        $this->call_time = now()->format('H:i');
        $this->direction = 'outbound';
        $this->status = 'done';
        
        if ($this->selectedDealId) {
            $this->deal_id = $this->selectedDealId;
        }

        $this->loadSelectedClientData();
        $this->autoSelectDealIfAvailable();
        $this->showCreateModal = true;
    }

    public function editCall($id)
    {
        $this->resetErrorBag();
        $call = ClientCall::findOrFail($id);

        if (!$this->checkCanEdit($call)) {
            $this->dispatch('notify', message: 'شما مجوز ویرایش این تماس را ندارید.', type: 'error');
            return;
        }

        $this->editingCallId = $call->id;
        $this->selectedClientId = $call->client_id;
        $this->loadSelectedClientData();

        $this->campaign_id = $call->campaign_id;
        $this->deal_id = $call->deal_id;
        $this->call_date_jalali = $this->formatGregorianToJalali($call->call_date) ?: Jalalian::now()->format('Y/m/d');
        $this->call_time = $call->call_time ? \Carbon\Carbon::parse($call->call_time)->format('H:i') : null;
        $this->duration_seconds = $call->duration_seconds;
        $this->direction = $call->direction ?? 'outbound';
        
        // Map legacy answered to done
        $this->status = ($call->status === 'answered') ? 'done' : $call->status;
        
        $this->reason = $call->reason;
        $this->result = $call->result;
        $this->next_action = $call->next_action;
        $this->next_action_date_jalali = $this->formatGregorianToJalali($call->next_action_date);
        $this->contact_phone = $call->contact_phone;
        $this->notes = $call->notes;

        $this->showCreateModal = true;
    }

    public function saveCall()
    {
        if (!$this->checkCanCreate() && !$this->editingCallId) {
            $this->dispatch('notify', message: 'شما مجوز ثبت تماس ندارید.', type: 'error');
            return;
        }

        $this->validate([
            'selectedClientId' => 'required|exists:clients,id',
            'call_date_jalali' => 'required|string',
            'call_time'        => 'required',
            'direction'        => 'required|in:inbound,outbound',
            'status'           => 'required|in:done,planned,no_answer,busy,cancelled,failed,answered',
            'reason'           => 'nullable|string|max:255',
            'result'           => 'nullable|string',
            'next_action'      => 'nullable|string|max:255',
            'next_action_date_jalali' => 'nullable|string',
            'campaign_id'      => 'nullable|exists:sales_campaigns,id',
            'deal_id'          => 'nullable|exists:sales_deals,id',
            'duration_seconds' => 'nullable|integer|min:0',
            'contact_phone'    => 'nullable|string|max:50',
            'notes'            => 'nullable|string',
        ], [], [
            'selectedClientId' => 'مشتری',
            'call_date_jalali' => 'تاریخ تماس',
            'call_time'        => 'زمان تماس',
            'status'           => 'وضعیت تماس',
        ]);

        $gregorianCallDate = $this->parseJalaliToGregorian($this->call_date_jalali) ?: today()->toDateString();
        $gregorianNextActionDate = $this->parseJalaliToGregorian($this->next_action_date_jalali);

        // Normalize 'answered' to 'done' for central compatibility
        $saveStatus = ($this->status === 'answered') ? 'done' : $this->status;

        if ($this->editingCallId) {
            $call = ClientCall::findOrFail($this->editingCallId);
            if (!$this->checkCanEdit($call)) {
                $this->dispatch('notify', message: 'شما مجوز ویرایش این تماس را ندارید.', type: 'error');
                return;
            }

            $call->fill([
                'client_id'        => $this->selectedClientId,
                'campaign_id'      => $this->campaign_id,
                'call_date'        => $gregorianCallDate,
                'call_time'        => $this->call_time,
                'duration_seconds' => $this->duration_seconds,
                'direction'        => $this->direction,
                'status'           => $saveStatus,
                'reason'           => $this->reason ?: ($this->direction === 'inbound' ? 'تماس ورودی' : 'تماس خروجی'),
                'result'           => $this->result,
                'next_action'      => $this->next_action,
                'next_action_date' => $gregorianNextActionDate,
                'contact_phone'    => $this->contact_phone,
                'notes'            => $this->notes,
            ]);
            $call->deal_id = $this->deal_id;
            $call->save();

            // If next action is specified, ensure followup task is updated/created without duplicate
            if ($this->next_action && $gregorianNextActionDate && class_exists(\Modules\Tasks\Entities\Task::class)) {
                $relatedType = \Modules\Tasks\Entities\Task::RELATED_TYPE_CLIENT;
                $relatedId = $this->selectedClientId;
                $meta = $this->deal_id ? ['deal_id' => (int)$this->deal_id] : [];

                $alreadyExists = \Modules\Tasks\Entities\Task::where('title', $this->next_action)
                    ->where(function ($q) use ($relatedId) {
                        $q->where('related_id', $relatedId)
                          ->orWhere('related_id', $this->deal_id);
                    })
                    ->whereDate('due_at', $gregorianNextActionDate)
                    ->exists();

                if (!$alreadyExists) {
                    \Modules\Tasks\Entities\Task::create([
                        'title'        => $this->next_action,
                        'description'  => 'پیگیری تماس ویرایش شده در تاریخ ' . $this->call_date_jalali,
                        'task_type'    => \Modules\Tasks\Entities\Task::TYPE_FOLLOW_UP,
                        'assignee_id'  => auth()->id(),
                        'creator_id'   => auth()->id(),
                        'status'       => \Modules\Tasks\Entities\Task::STATUS_TODO,
                        'priority'     => \Modules\Tasks\Entities\Task::PRIORITY_HIGH,
                        'due_at'       => $gregorianNextActionDate . ' 10:00:00',
                        'related_type' => $relatedType,
                        'related_id'   => $relatedId,
                        'meta'         => $meta,
                    ]);
                }
            }

            $this->showCreateModal = false;
            $this->editingCallId = null;
            $this->dispatch('refreshStats');
            $this->dispatch('notify', message: 'تماس با موفقیت ویرایش شد', type: 'success');
            return;
        }

        // Create new Call in client_calls table
        $call = new ClientCall();
        $call->fill([
            'client_id'        => $this->selectedClientId,
            'campaign_id'      => $this->campaign_id,
            'user_id'          => auth()->id(),
            'call_date'        => $gregorianCallDate,
            'call_time'        => $this->call_time,
            'duration_seconds' => $this->duration_seconds,
            'direction'        => $this->direction,
            'status'           => $saveStatus,
            'reason'           => $this->reason ?: ($this->direction === 'inbound' ? 'تماس ورودی' : 'تماس خروجی'),
            'result'           => $this->result,
            'next_action'      => $this->next_action,
            'next_action_date' => $gregorianNextActionDate,
            'contact_phone'    => $this->contact_phone,
            'notes'            => $this->notes,
        ]);
        $call->deal_id = $this->deal_id;
        $call->save();

        // Note: Event listener in SalesServiceProvider automatically invokes SalesAutomationService safely!

        $this->showCreateModal = false;
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'تماس با موفقیت در سیستم ثبت شد', type: 'success');
    }

    public function deleteCall($id)
    {
        $call = ClientCall::findOrFail($id);

        if (!$this->checkCanDelete($call)) {
            $this->dispatch('notify', message: 'شما مجوز حذف این تماس را ندارید.', type: 'error');
            return;
        }

        $call->delete();
        $this->dispatch('refreshStats');
        $this->dispatch('notify', message: 'تماس با موفقیت حذف شد', type: 'success');
    }

    public function startQuickCall($status)
    {
        if (!$this->checkCanCreate()) {
            $this->dispatch('notify', message: 'شما مجوز ثبت تماس ندارید.', type: 'error');
            return;
        }

        if (!$this->selectedClientId) {
            $this->dispatch('notify', message: 'لطفاً ابتدا یک مشتری را انتخاب کنید.', type: 'warning');
            return;
        }

        if (class_exists(\Modules\Clients\Entities\Client::class)) {
            $client = \Modules\Clients\Entities\Client::find($this->selectedClientId);
            if ($client) {
                // Map 'answered' to 'done'
                $saveStatus = ($status === 'answered') ? 'done' : $status;

                ClientCall::create([
                    'client_id'     => $this->selectedClientId,
                    'deal_id'       => $this->selectedDealId,
                    'user_id'       => auth()->id(),
                    'call_date'     => today()->toDateString(),
                    'call_time'     => now()->format('H:i'),
                    'direction'     => 'outbound',
                    'status'        => $saveStatus,
                    'reason'        => 'تماس سریع خروجی',
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
        $this->clientSearch = '';
        $this->clientSearchResults = [];
    }

    public function selectClient($id)
    {
        $this->dispatch('clientSelected', clientId: $id);
    }

    public function initiateVoipCall(string $phone, ?int $clientId = null): void
    {
        if ($clientId) {
            $this->dispatch('clientSelected', clientId: $clientId);
        }
        $this->dispatch('initiateVoip', phone: $phone);
        $this->dispatch('notify', message: 'در حال برقراری تماس صوتی...', type: 'info');
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

    protected function checkCanCreate(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super-admin')
            || $user->can('client-calls.create')
            || $user->can('sales.calls.create')
            || $user->can('sales.manage');
    }

    protected function checkCanEdit(ClientCall $call): bool
    {
        $user = auth()->user();
        if ($user->hasRole('super-admin') || $user->can('client-calls.edit') || $user->can('sales.calls.edit') || $user->can('sales.manage')) {
            return true;
        }
        return $call->user_id === $user->id;
    }

    protected function checkCanDelete(ClientCall $call): bool
    {
        $user = auth()->user();
        if ($user->hasRole('super-admin') || $user->can('client-calls.delete') || $user->can('sales.calls.delete') || $user->can('sales.manage')) {
            return true;
        }
        return $call->user_id === $user->id;
    }

    protected function parseJalaliToGregorian(?string $jalaliDate): ?string
    {
        if (!$jalaliDate) return null;
        try {
            $clean = str_replace('-', '/', trim($jalaliDate));
            return Jalalian::fromFormat('Y/m/d', $clean)->toCarbon()->toDateString();
        } catch (\Throwable $e) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $jalaliDate)) {
                return $jalaliDate;
            }
            return null;
        }
    }

    protected function formatGregorianToJalali($date): ?string
    {
        if (!$date) return null;
        try {
            return Jalalian::fromDateTime($date)->format('Y/m/d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function render()
    {
        $user = auth()->user();

        // Scope query using ClientCall visibleForUser, or sales permission fallback
        $query = ClientCall::query()->with(['client', 'user']);

        if (!$user->hasRole('super-admin') && !$user->can('client-calls.view.all') && !$user->can('sales.calls.view.all') && !$user->can('sales.manage')) {
            $query->visibleForUser($user);
        }

        if ($this->selectedClientId && $this->filterClientMode === 'active') {
            $query->where('client_id', $this->selectedClientId);
        }

        if ($this->filterStatus) {
            if ($this->filterStatus === 'done') {
                $query->whereIn('status', ['done', 'answered']);
            } elseif ($this->filterStatus === 'failed') {
                $query->where('status', 'failed');
            } elseif ($this->filterStatus === 'cancelled') {
                $query->whereIn('status', ['cancelled', 'canceled']);
            } else {
                $query->where('status', $this->filterStatus);
            }
        }

        if ($this->filterDate === 'today') {
            $query->today();
        } elseif ($this->filterDate === 'week') {
            $query->thisWeek();
        } elseif ($this->filterDate === 'month') {
            $query->whereMonth('call_date', now()->month)
                  ->whereYear('call_date', now()->year);
        }

        if ($this->search && !$this->selectedClientId) {
            $query->where(function($q) {
                $q->where('reason', 'like', '%'.$this->search.'%')
                  ->orWhere('result', 'like', '%'.$this->search.'%')
                  ->orWhere('contact_phone', 'like', '%'.$this->search.'%')
                  ->orWhere('notes', 'like', '%'.$this->search.'%')
                  ->orWhereHas('client', function($sub) {
                      $sub->where('full_name', 'like', '%'.$this->search.'%')
                          ->orWhere('phone', 'like', '%'.$this->search.'%');
                  });
            });
        }

        $calls = $query->latest('call_date')->latest('call_time')->paginate(10);
        $campaigns = Campaign::where('status', 'active')->get();

        // Call KPI stats for today
        $kpiQuery = ClientCall::query();
        if (!$user->hasRole('super-admin') && !$user->can('client-calls.view.all') && !$user->can('sales.calls.view.all') && !$user->can('sales.manage')) {
            $kpiQuery->visibleForUser($user);
        }
        if ($this->selectedClientId && $this->filterClientMode === 'active') {
            $kpiQuery->where('client_id', $this->selectedClientId);
        }

        $todayCallsStats = (clone $kpiQuery)->whereDate('call_date', today())->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status IN ('done', 'answered') THEN 1 ELSE 0 END) as done,
            SUM(CASE WHEN status = 'planned' THEN 1 ELSE 0 END) as planned,
            SUM(CASE WHEN status IN ('no_answer', 'busy', 'failed') THEN 1 ELSE 0 END) as missed
        ")->first();

        $callKpis = [
            'total' => (int) ($todayCallsStats->total ?? 0),
            'done' => (int) ($todayCallsStats->done ?? 0),
            'planned' => (int) ($todayCallsStats->planned ?? 0),
            'missed' => (int) ($todayCallsStats->missed ?? 0),
            'today_total' => (int) ($todayCallsStats->total ?? 0),
            'today_done' => (int) ($todayCallsStats->done ?? 0),
            'today_planned' => (int) ($todayCallsStats->planned ?? 0),
            'today_missed' => (int) ($todayCallsStats->missed ?? 0),
        ];

        // Map deals for calls that have deal_id
        $dealIds = $calls->pluck('deal_id')->filter()->unique()->toArray();
        $dealsMap = !empty($dealIds) ? SalesDeal::whereIn('id', $dealIds)->pluck('title', 'id')->toArray() : [];

        return view('sales::livewire.call-center-tab', [
            'calls' => $calls,
            'campaigns' => $campaigns,
            'availableDeals' => $this->availableDeals,
            'callKpis' => $callKpis,
            'dealsMap' => $dealsMap,
            'filterDate' => $this->filterDate,
            'filterClientMode' => $this->filterClientMode,
            'selectedClientId' => $this->selectedClientId,
            'selectedClientName' => $this->selectedClientName,
        ]);
    }
}
