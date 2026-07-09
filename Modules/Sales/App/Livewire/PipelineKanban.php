<?php

declare(strict_types=1);

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Modules\Sales\App\Models\SalesPipeline;
use Modules\Sales\App\Models\SalesDeal;
use Modules\Sales\App\Models\SalesLossReason;
use Modules\Clients\Entities\Client;
use App\Models\User;

class PipelineKanban extends Component
{
    public bool $onlyMyDeals = false;
    public ?int $selectedCampaignId = null;
    public string $search = '';

    // Modals
    public bool $showCreateModal = false;
    public bool $showLossReasonModal = false;

    // Create Deal Form Fields
    public string $newDealTitle = '';
    public ?int $newDealClientId = null;
    public ?int $newDealStageId = null;
    public float $newDealExpectedRevenue = 0.0;
    public ?int $newDealProbability = null;
    public ?string $newDealExpectedCloseDate = null;
    public ?string $newDealSource = null;
    public ?int $newDealUserId = null;
    public string $newDealDescription = '';

    // Loss Reason Form
    public ?int $pendingLostDealId = null;
    public ?int $lossReasonId = null;

    // Dropdowns data
    public $clients = [];
    public $users = [];
    public $campaigns = [];
    public $lossReasons = [];

    protected $queryString = ['onlyMyDeals', 'selectedCampaignId', 'search'];

    public function mount()
    {
        $this->seedDefaultStagesIfNeeded();
        $this->loadDropdowns();
        $this->newDealUserId = auth()->id();
    }

    private function seedDefaultStagesIfNeeded()
    {
        if (SalesPipeline::count() === 0) {
            SalesPipeline::create(['name' => 'ارتباط اولیه', 'color' => '#3b82f6', 'order' => 1]);
            SalesPipeline::create(['name' => 'پرزنت / دمو', 'color' => '#a855f7', 'order' => 2]);
            SalesPipeline::create(['name' => 'مذاکره', 'color' => '#eab308', 'order' => 3]);
            SalesPipeline::create(['name' => 'پیش‌فاکتور', 'color' => '#f97316', 'order' => 4]);
            SalesPipeline::create(['name' => 'موفق (Won)', 'color' => '#22c55e', 'order' => 5, 'is_won' => true]);
            SalesPipeline::create(['name' => 'ناموفق (Lost)', 'color' => '#ef4444', 'order' => 6, 'is_lost' => true]);
        }

        if (SalesLossReason::count() === 0) {
            SalesLossReason::create(['reason_key' => 'price', 'reason_text' => 'قیمت بالا']);
            SalesLossReason::create(['reason_key' => 'competitor', 'reason_text' => 'انتخاب رقیب']);
            SalesLossReason::create(['reason_key' => 'no_response', 'reason_text' => 'عدم پاسخگویی']);
            SalesLossReason::create(['reason_key' => 'no_need', 'reason_text' => 'عدم نیاز مشتری']);
            SalesLossReason::create(['reason_key' => 'feature_gap', 'reason_text' => 'نقص در امکانات']);
        }
    }

    public function loadDropdowns()
    {
        if (class_exists(Client::class)) {
            $this->clients = Client::orderBy('full_name')->get(['id', 'full_name', 'phone']);
        }
        $this->users = User::orderBy('name')->get(['id', 'name']);
        $this->lossReasons = SalesLossReason::where('is_active', true)->get();
        
        $campaignClass = '\Modules\Sales\App\Models\Campaign';
        if (class_exists($campaignClass)) {
            $this->campaigns = $campaignClass::orderBy('name')->get(['id', 'name']);
        }
    }

    public function moveDeal(int $dealId, int $targetStageId)
    {
        $deal = SalesDeal::find($dealId);
        if (!$deal) return;

        $targetStage = SalesPipeline::find($targetStageId);
        if (!$targetStage) return;

        $deal->pipeline_stage_id = $targetStageId;
        $deal->stage_entered_at = now();

        if ($targetStage->is_won) {
            $deal->status = 'won';
            $deal->actual_revenue = $deal->expected_revenue; // auto-fill final revenue
            $deal->loss_reason_id = null;
            $deal->save();
            $this->dispatch('notify', message: 'پرونده با موفقیت بسته شد (پیروزی).', type: 'success');
        } elseif ($targetStage->is_lost) {
            // Require loss reason
            $this->pendingLostDealId = $dealId;
            $this->lossReasonId = null;
            $this->showLossReasonModal = true;
            return;
        } else {
            $deal->status = 'open';
            $deal->actual_revenue = null;
            $deal->loss_reason_id = null;
            $deal->save();
        }

        $this->dispatch('notify', message: 'مرحله پرونده به‌روزرسانی شد.', type: 'info');
    }

    public function submitLossReason()
    {
        $this->validate([
            'lossReasonId' => 'required|exists:sales_loss_reasons,id'
        ]);

        $deal = SalesDeal::find($this->pendingLostDealId);
        if ($deal) {
            $deal->status = 'lost';
            $deal->loss_reason_id = $this->lossReasonId;
            $deal->save();

            $this->dispatch('notify', message: 'وضعیت پرونده به شکست تغییر یافت.', type: 'warning');
        }

        $this->showLossReasonModal = false;
        $this->pendingLostDealId = null;
    }

    public function openCreateModal(?int $stageId = null)
    {
        $this->resetCreateForm();
        if ($stageId) {
            $this->newDealStageId = $stageId;
        } else {
            $firstStage = SalesPipeline::orderBy('order')->first();
            $this->newDealStageId = $firstStage?->id;
        }
        $this->showCreateModal = true;
    }

    public function resetCreateForm()
    {
        $this->newDealTitle = '';
        $this->newDealClientId = null;
        $this->newDealExpectedRevenue = 0.0;
        $this->newDealProbability = null;
        $this->newDealExpectedCloseDate = null;
        $this->newDealSource = '';
        $this->newDealUserId = auth()->id();
        $this->newDealDescription = '';
    }

    public function saveDeal()
    {
        $this->validate([
            'newDealTitle' => 'required|string|max:191',
            'newDealClientId' => 'required|exists:clients,id',
            'newDealStageId' => 'required|exists:sales_pipelines,id',
            'newDealExpectedRevenue' => 'required|numeric|min:0',
            'newDealProbability' => 'nullable|integer|min:0|max:100',
            'newDealExpectedCloseDate' => 'nullable|date',
            'newDealUserId' => 'required|exists:users,id',
        ]);

        $stage = SalesPipeline::find($this->newDealStageId);
        $status = 'open';
        $actualRevenue = null;
        if ($stage?->is_won) {
            $status = 'won';
            $actualRevenue = $this->newDealExpectedRevenue;
        } elseif ($stage?->is_lost) {
            $status = 'lost';
        }

        SalesDeal::create([
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
            'created_by' => auth()->id(),
        ]);

        $this->showCreateModal = false;
        $this->resetCreateForm();
        $this->dispatch('notify', message: 'پرونده فروش جدید با موفقیت ایجاد شد.', type: 'success');
    }

    public function initiateVoip(string $phone, int $clientId)
    {
        $this->dispatch('voip-initiate', phone: $phone, clientId: $clientId);
        $this->dispatch('notify', message: 'درحال برقراری تماس با مشتری...', type: 'info');
    }

    public function render()
    {
        $stages = SalesPipeline::orderBy('order')->get();
        $user = auth()->user();

        // Get deals and map them by stage_id
        $dealsQuery = SalesDeal::with(['client', 'owner'])
            ->visibleForUser($user);

        if ($this->onlyMyDeals) {
            $dealsQuery->where('user_id', $user->id);
        }

        if ($this->selectedCampaignId) {
            $dealsQuery->whereHas('calls', function ($q) {
                $q->where('campaign_id', $this->selectedCampaignId);
            });
        }

        if (!empty($this->search)) {
            $dealsQuery->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhereHas('client', function ($sub) {
                      $sub->where('full_name', 'like', '%' . $this->search . '%')
                          ->orWhere('phone', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $allDeals = $dealsQuery->get();

        $dealsByStage = [];
        foreach ($stages as $stage) {
            $dealsByStage[$stage->id] = $allDeals->where('pipeline_stage_id', $stage->id);
        }

        return view('sales::livewire.pipeline-kanban', [
            'stages' => $stages,
            'dealsByStage' => $dealsByStage
        ]);
    }
}
