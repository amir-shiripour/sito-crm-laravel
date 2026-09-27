<?php

namespace Modules\Booking\App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Modules\Booking\Entities\BookingLaboratoryOrder;
use Modules\Booking\Entities\BookingLaboratoryStage;
use Modules\Booking\Entities\BookingLaboratoryDailyLog;
use Modules\Booking\Services\LaboratoryService;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

#[Layout('layouts.user')]
class LaboratoryDailyBoard extends Component
{
    use WithPagination;

    public string $selectedDateJalali = '';
    public string $search = '';
    public string $filterOnlyPending = '1'; // '1' = only cases needing action today, '0' = all active

    // Quick Log Modal
    public bool $showLogModal = false;
    public ?int $activeOrderId = null;
    public ?int $activeStageId = null;
    public string $patientTitle = '';
    public string $currentStageTitle = '';
    public bool $modalNeedsFollowup = true;
    public string $modalResult = '';

    public ?string $toastSuccess = null;

    public function mount(): void
    {
        $this->selectedDateJalali = Jalalian::now()->format('Y/m/d');
    }

    public function openLogModal(int $orderId, ?int $stageId = null): void
    {
        $order = BookingLaboratoryOrder::with('stages')->find($orderId);
        if (!$order) return;

        $this->activeOrderId = $orderId;
        $this->activeStageId = $stageId;
        $this->patientTitle = $order->patient_name . ($order->patient_file_number ? " ({$order->patient_file_number})" : '');
        
        $stage = $stageId ? $order->stages->firstWhere('id', $stageId) : $order->stages->where('is_completed', false)->first();
        $this->currentStageTitle = $stage ? $stage->stage_title : 'پیگیری کلی';
        $this->modalNeedsFollowup = true;
        $this->modalResult = '';

        $this->showLogModal = true;
    }

    public function saveDailyLog(): void
    {
        if (!$this->activeOrderId) return;

        $order = BookingLaboratoryOrder::find($this->activeOrderId);
        $stage = $this->activeStageId ? BookingLaboratoryStage::find($this->activeStageId) : null;

        if ($order) {
            $service = app(LaboratoryService::class);
            $service->logDailyFollowup(
                $order,
                $stage,
                $this->modalNeedsFollowup,
                trim($this->modalResult) ?: 'پیگیری انجام شد.',
                auth()->id()
            );

            // If user checked that this stage is complete, complete it
            if ($stage && !$stage->is_completed && !empty($this->modalResult)) {
                $stage->markCompleted(trim($this->modalResult), auth()->id());
            }

            $this->toastSuccess = "نتیجه پیگیری بیمار {$order->patient_name} با موفقیت ثبت شد.";
        }

        $this->showLogModal = false;
        $this->activeOrderId = null;
        $this->activeStageId = null;
    }

    public function render()
    {
        // Find orders that are active and not yet received
        $query = BookingLaboratoryOrder::with(['stages', 'client', 'doctor', 'dailyLogs'])
            ->where('status', '!=', BookingLaboratoryOrder::STATUS_RECEIVED)
            ->where('status', '!=', BookingLaboratoryOrder::STATUS_CANCELED);

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('patient_name', 'like', "%{$s}%")
                  ->orWhere('patient_file_number', 'like', "%{$s}%")
                  ->orWhere('order_number', 'like', "%{$s}%");
            });
        }

        $orders = $query->orderBy('expected_delivery_at')->get();

        // Evaluate "needs_followup" today for each order
        $todayJalali = Jalalian::now()->format('Y/m/d');

        $rows = $orders->map(function ($order) use ($todayJalali) {
            // Check if any stage is due today or overdue
            $dueStage = null;
            $needsFollowup = false;

            foreach ($order->stages as $stage) {
                if (!$stage->is_completed) {
                    if ($stage->due_at && ($stage->due_at->isToday() || $stage->due_at->isPast())) {
                        $dueStage = $stage;
                        $needsFollowup = true;
                        break;
                    }
                }
            }

            // If no stage overdue/today, get the earliest pending stage
            if (!$dueStage) {
                $dueStage = $order->stages->where('is_completed', false)->first();
            }

            // Check if there is already a log for today
            $todayLog = $order->dailyLogs->first(function ($l) {
                return $l->log_date && $l->log_date->isToday();
            });

            return (object) [
                'order'          => $order,
                'dueStage'       => $dueStage,
                'needsFollowup'  => $needsFollowup,
                'todayLog'       => $todayLog,
                'isOverdue'      => $order->isOverdue(),
            ];
        });

        // If filterOnlyPending is enabled, prioritize orders needing followup today or overdue
        if ($this->filterOnlyPending === '1') {
            $rows = $rows->filter(function ($item) {
                return $item->needsFollowup || $item->isOverdue;
            });
        }

        return view('booking::user.laboratory.daily-board', [
            'rows'        => $rows,
            'todayJalali' => $todayJalali,
        ]);
    }
}
