<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Market\App\Models\OrderSyncLog;
use Modules\Market\App\Services\ClientSyncService;
use Illuminate\Support\Facades\Auth;

class PendingSyncs extends Component
{
    use WithPagination;

    public array $selectedLogs = [];

    public function approve(int $logId, ClientSyncService $syncService): void
    {
        $syncService->approveLog($logId, Auth::id());
        $this->resetPage();
        $this->dispatch('notify', type: 'success', text: 'تغییر با موفقیت تایید و اعمال شد.');
    }

    public function reject(int $logId, ClientSyncService $syncService): void
    {
        $syncService->rejectLog($logId, Auth::id());
        $this->resetPage();
        $this->dispatch('notify', type: 'info', text: 'درخواست تغییر رد شد.');
    }

    public function approveSelected(ClientSyncService $syncService): void
    {
        foreach ($this->selectedLogs as $logId) {
            $syncService->approveLog((int)$logId, Auth::id());
        }
        $this->selectedLogs = [];
        $this->resetPage();
        $this->dispatch('notify', type: 'success', text: count($this->selectedLogs) . ' تغییر با موفقیت تایید شد.');
    }

    public function rejectSelected(ClientSyncService $syncService): void
    {
        foreach ($this->selectedLogs as $logId) {
            $syncService->rejectLog((int)$logId, Auth::id());
        }
        $this->selectedLogs = [];
        $this->resetPage();
        $this->dispatch('notify', type: 'info', text: count($this->selectedLogs) . ' تغییر رد شد.');
    }

    public function approveAll(ClientSyncService $syncService): void
    {
        $logs = OrderSyncLog::where('status', 'pending')->get();
        foreach ($logs as $log) {
            $syncService->approveLog($log->id, Auth::id());
        }
        $this->resetPage();
        $this->dispatch('notify', type: 'success', text: 'تمام تغییرات معلق تایید شدند.');
    }

    public function render()
    {
        $pendingLogs = OrderSyncLog::with(['client', 'order'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);

        return view('market::livewire.admin.pending-syncs', [
            'pendingLogs' => $pendingLogs,
        ]);
    }
}
