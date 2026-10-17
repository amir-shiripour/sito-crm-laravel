<?php

namespace Modules\FieldService\App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\FieldService\App\Enums\SettlementStatus;
use Modules\FieldService\App\Models\PayaSettlement;

class SettlementsManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    public function approve(int $settlementId)
    {
        $settlement = PayaSettlement::find($settlementId);
        if ($settlement) {
            $settlement->status = SettlementStatus::COMPLETED;
            $settlement->processed_at = now();
            $settlement->approved_by = Auth::id();
            $settlement->save();

            $this->dispatch('notify', [
                'type' => 'success',
                'text' => "تسویه حساب کد {$settlement->tracking_code} به مبلغ " . number_format($settlement->amount) . " ریال تایید شد.",
            ]);
        }
    }

    public function reject(int $settlementId)
    {
        $settlement = PayaSettlement::find($settlementId);
        if ($settlement) {
            $settlement->status = SettlementStatus::REJECTED;
            $settlement->save();

            $this->dispatch('notify', [
                'type' => 'warning',
                'text' => "درخواست تسویه کد {$settlement->tracking_code} رد شد.",
            ]);
        }
    }

    public function render()
    {
        $query = PayaSettlement::query()->with(['technician', 'approver'])->latest();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('tracking_code', 'like', "%{$this->search}%")
                  ->orWhere('account_owner_name', 'like', "%{$this->search}%")
                  ->orWhere('shaba_number', 'like', "%{$this->search}%");
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $settlements = $query->paginate(12);

        $pendingTotal = PayaSettlement::where('status', SettlementStatus::PENDING)->sum('amount');
        $completedTotal = PayaSettlement::where('status', SettlementStatus::COMPLETED)->sum('amount');

        return view('fieldservice::livewire.admin.settlements-manager', [
            'settlements'    => $settlements,
            'pendingTotal'   => $pendingTotal,
            'completedTotal' => $completedTotal,
        ]);
    }
}
