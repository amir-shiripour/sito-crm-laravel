<?php

namespace Modules\FieldService\App\Livewire\Technician;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\FieldService\App\Models\Technician;

class TechnicianContractsPanel extends Component
{
    public bool $compactPortalMode = false;
    public ?int $viewingContractId = null;
    public bool $showContractModal = false;
    public bool $agreeTerms = false;
    public ?string $successMessage = null;
    public ?string $errorMessage = null;

    protected $listeners = [
        'open-pending-contract-modal' => 'openFirstPendingContract',
        'contract-signed'             => '$refresh',
    ];

    public function mount(bool $compactPortalMode = false): void
    {
        $this->compactPortalMode = $compactPortalMode;
    }

    protected function isContractForgeActive(): bool
    {
        return class_exists(\Modules\ContractForge\App\Models\Contract::class)
            && class_exists(\Modules\ContractForge\Services\ContractModuleHelper::class)
            && \Modules\ContractForge\Services\ContractModuleHelper::isModuleActive('ContractForge');
    }

    protected function getTechnician(): ?Technician
    {
        $user = Auth::user();
        return $user ? Technician::where('user_id', $user->id)->first() : null;
    }

    public function openContract(int $contractId): void
    {
        $this->errorMessage = null;
        $this->agreeTerms = false;
        $this->viewingContractId = $contractId;
        $this->showContractModal = true;
    }

    public function openFirstPendingContract(): void
    {
        $tech = $this->getTechnician();
        if (! $tech || ! $this->isContractForgeActive()) {
            return;
        }

        $pending = $tech->contracts()->where('status', 'active')->latest('id')->first();
        if ($pending) {
            $this->openContract($pending->id);
        }
    }

    public function closeContractModal(): void
    {
        $this->showContractModal = false;
        $this->viewingContractId = null;
        $this->agreeTerms = false;
        $this->errorMessage = null;
    }

    public function signContract(): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        if (! $this->isContractForgeActive()) {
            return;
        }

        $tech = $this->getTechnician();
        if (! $tech || ! $this->viewingContractId) {
            $this->errorMessage = 'قرارداد معتبر یافت نشد.';
            return;
        }

        if (! $this->agreeTerms) {
            $this->errorMessage = 'لطفاً تیک مطالعه و پذیرش مفاد قرارداد را بزنید.';
            return;
        }

        $contract = \Modules\ContractForge\App\Models\Contract::where('id', $this->viewingContractId)
            ->where('contractable_type', Technician::class)
            ->where('contractable_id', $tech->id)
            ->first();

        if (! $contract || $contract->status !== 'active') {
            $this->errorMessage = 'این قرارداد در وضعیت قابل امضا نیست.';
            return;
        }

        $meta = is_array($contract->meta) ? $contract->meta : [];
        $meta['signed_by_user_id'] = Auth::id();
        $meta['signed_ip'] = request()->ip();
        $meta['signed_user_agent'] = request()->userAgent();
        $meta['confirmed_via'] = $this->compactPortalMode ? 'technician_portal' : 'user_profile';

        $contract->update([
            'status'    => 'signed',
            'signed_at' => now(),
            'meta'      => $meta,
        ]);

        $this->showContractModal = false;
        $this->viewingContractId = null;
        $this->agreeTerms = false;
        $this->successMessage = "قرارداد {$contract->contract_number} با موفقیت تایید و امضا شد.";

        $this->dispatch('contract-signed');
        $this->dispatch('notify', [
            'type' => 'success',
            'text' => $this->successMessage,
        ]);
    }

    public function render()
    {
        $tech = $this->getTechnician();
        $active = $this->isContractForgeActive();

        $contracts = ($tech && $active)
            ? $tech->contracts()->whereIn('status', ['active', 'signed'])->latest('id')->get()
            : collect();

        $pendingCount = $contracts->where('status', 'active')->count();

        $viewingContract = ($tech && $active && $this->viewingContractId)
            ? $tech->contracts()->where('id', $this->viewingContractId)->first()
            : null;

        return view('fieldservice::livewire.technician.technician-contracts-panel', [
            'contracts'       => $contracts,
            'pendingCount'    => $pendingCount,
            'viewingContract' => $viewingContract,
        ]);
    }
}
