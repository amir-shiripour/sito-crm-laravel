<?php

namespace Modules\Booking\App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Modules\Booking\Entities\BookingLaboratoryOrder;
use Modules\Booking\Entities\BookingLaboratoryStage;
use Modules\Booking\Entities\BookingLaboratoryDailyLog;
use Modules\Booking\Services\LaboratoryService;
use App\Models\User;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

#[Layout('layouts.user')]
class LaboratoryDailyBoard extends Component
{
    use WithPagination;

    public string $selectedDateJalali = '';
    public string $search = '';
    public string $kpiFilter = 'daily_all'; // 'daily_all' (default), 'overdue', 'due_today', 'completed_today', 'upcoming', 'all'
    public string $selectedLabPartner = '';
    public string $selectedDoctorId = '';

    // Quick Log Modal
    public bool $showLogModal = false;
    public ?int $activeOrderId = null;
    public ?int $activeStageId = null;
    public string $patientTitle = '';
    public string $currentStageTitle = '';
    public bool $modalNeedsFollowup = true;
    public string $modalResult = '';
    public string $modalActionType = 'log_only'; // 'log_only', 'complete_stage', 'postpone_stage'
    public int $modalPostponeDays = 2;
    public $modalOrder = null;

    // Full Details Modal
    public bool $showDetailModal = false;
    public ?int $detailOrderId = null;
    public string $detailModalTab = 'overview'; // 'overview', 'dental_chart', 'logs'

    // Quick Stage Completion Modal
    public bool $showStageModal = false;
    public ?int $stageToCompleteId = null;
    public string $stageNote = '';

    // Receive Order Modal (Make green)
    public bool $showReceiveModal = false;
    public ?int $orderToReceiveId = null;
    public string $receiveNote = '';

    public ?string $toastSuccess = null;

    public function mount(): void
    {
        $this->selectedDateJalali = Jalalian::now()->format('Y/m/d');
    }

    public function setDateToday(): void
    {
        $this->selectedDateJalali = Jalalian::now()->format('Y/m/d');
    }

    public function setDateYesterday(): void
    {
        $this->selectedDateJalali = Jalalian::now()->subDays(1)->format('Y/m/d');
    }

    public function setDateTomorrow(): void
    {
        $this->selectedDateJalali = Jalalian::now()->addDays(1)->format('Y/m/d');
    }

    public function setKpiFilter(string $filter): void
    {
        $this->kpiFilter = $filter;
    }

    public function openLogModal(int $orderId, ?int $stageId = null): void
    {
        $order = BookingLaboratoryOrder::with(['stages', 'dailyLogs.operator', 'client', 'doctor'])->find($orderId);
        if (!$order) return;

        $this->modalOrder = $order;
        $this->activeOrderId = $orderId;
        $this->activeStageId = $stageId;
        $this->patientTitle = $order->patient_name . ($order->patient_file_number ? " ({$order->patient_file_number})" : '');
        
        $stage = $stageId ? $order->stages->firstWhere('id', $stageId) : $order->stages->where('is_completed', false)->first();
        $this->currentStageTitle = $stage ? $stage->stage_title : 'پیگیری کلی سفارش';
        $this->modalNeedsFollowup = true;
        $this->modalResult = '';
        $this->modalActionType = 'log_only';
        $this->modalPostponeDays = 2;

        $this->showLogModal = true;
    }

    public function saveDailyLog(): void
    {
        if (!$this->activeOrderId) return;

        $order = BookingLaboratoryOrder::with('stages')->find($this->activeOrderId);
        $stage = $this->activeStageId ? BookingLaboratoryStage::find($this->activeStageId) : null;

        if (!$stage && $order) {
            $stage = $order->stages->where('is_completed', false)->first();
        }

        if ($order) {
            $service = app(LaboratoryService::class);
            $note = trim($this->modalResult) ?: 'پیگیری و تماس انجام شد.';

            if ($this->modalActionType === 'complete_stage' && $stage) {
                $service->completeStage($stage, $note, auth()->id());
                $this->toastSuccess = "مرحله «{$stage->stage_title}» برای {$order->patient_name} با موفقیت تایید و پیگیری ثبت شد.";
            } elseif ($this->modalActionType === 'postpone_stage' && $stage) {
                $service->postponeStage($stage, $this->modalPostponeDays, $note, auth()->id());
                $this->toastSuccess = "موعد مرحله «{$stage->stage_title}» به مدت {$this->modalPostponeDays} روز تمدید و نتیجه تماس ثبت شد.";
            } else {
                $service->logDailyFollowup(
                    $order,
                    $stage,
                    $this->modalNeedsFollowup,
                    $note,
                    auth()->id()
                );
                $this->toastSuccess = "نتیجه پیگیری بیمار {$order->patient_name} با موفقیت ثبت شد.";
            }
        }

        $this->showLogModal = false;
        $this->activeOrderId = null;
        $this->activeStageId = null;
        $this->modalOrder = null;
    }

    public function openStageModal(int $stageId): void
    {
        $this->stageToCompleteId = $stageId;
        $this->stageNote = '';
        $this->showStageModal = true;
    }

    public function confirmStageCompletion(): void
    {
        if (!$this->stageToCompleteId) return;

        $stage = BookingLaboratoryStage::with('order')->find($this->stageToCompleteId);
        if ($stage) {
            app(LaboratoryService::class)->completeStage($stage, $this->stageNote ?: null, auth()->id());
            $this->toastSuccess = "مرحله «{$stage->stage_title}» با موفقیت تکمیل شد.";
        }

        $this->showStageModal = false;
        $this->stageToCompleteId = null;
    }

    public function openReceiveModal(int $orderId): void
    {
        $this->orderToReceiveId = $orderId;
        $this->receiveNote = '';
        $this->showReceiveModal = true;
    }

    public function confirmReceiveOrder(): void
    {
        if (!$this->orderToReceiveId) return;

        $order = BookingLaboratoryOrder::find($this->orderToReceiveId);
        if ($order) {
            app(LaboratoryService::class)->markOrderReceived($order, $this->receiveNote ?: null, auth()->id());
            $this->toastSuccess = "سفارش {$order->patient_name} دریافت شد و وضعیت آن به سبز (تکمیل در مطب) تغییر یافت.";
        }

        $this->showReceiveModal = false;
        $this->orderToReceiveId = null;
    }

    public function openDetailModal(int $orderId, string $tab = 'overview'): void
    {
        $this->detailOrderId = $orderId;
        $this->detailModalTab = $tab;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailOrderId = null;
    }

    public function setDetailTab(string $tab): void
    {
        $this->detailModalTab = $tab;
    }

    public function render()
    {
        // Parse selected Jalali date into Carbon instance for comparison
        $selectedCarbon = null;
        try {
            if (!empty($this->selectedDateJalali)) {
                $parts = explode('/', trim($this->selectedDateJalali));
                if (count($parts) === 3) {
                    $selectedCarbon = (new Jalalian((int)$parts[0], (int)$parts[1], (int)$parts[2]))->toCarbon()->startOfDay();
                }
            }
        } catch (\Exception $e) {
            $selectedCarbon = Carbon::today()->startOfDay();
        }
        if (!$selectedCarbon) {
            $selectedCarbon = Carbon::today()->startOfDay();
        }

        // Base active orders query
        $activeOrders = BookingLaboratoryOrder::with(['stages', 'client', 'doctor', 'dailyLogs.operator'])
            ->where('status', '!=', BookingLaboratoryOrder::STATUS_RECEIVED)
            ->where('status', '!=', BookingLaboratoryOrder::STATUS_CANCELED)
            ->get();

        // Process all active orders against the selected date
        $allRows = $activeOrders->map(function ($order) use ($selectedCarbon) {
            $incompleteStages = $order->stages->where('is_completed', false)->sortBy('sort_order');
            $currentStage = $incompleteStages->first();
            
            $isOverdue = false;
            $isDueOnDate = false;
            $statusText = '';
            $statusType = 'upcoming';
            $priority = 4;
            $diffDays = 0;

            if ($currentStage && $currentStage->due_at) {
                $due = $currentStage->due_at;
                if ($due->isSameDay($selectedCarbon)) {
                    $isDueOnDate = true;
                    $statusType = 'due_today';
                    $statusText = 'موعد امروز';
                    $priority = 2;
                } elseif ($due->lt($selectedCarbon)) {
                    $isOverdue = true;
                    $statusType = 'overdue';
                    $diffDays = $due->copy()->startOfDay()->diffInDays($selectedCarbon);
                    $statusText = ($diffDays ?: 1) . ' روز تاخیر';
                    $priority = 1;
                } else {
                    $statusType = 'upcoming';
                    $diffDays = $selectedCarbon->diffInDays($due->copy()->startOfDay());
                    $statusText = $diffDays . ' روز مانده';
                    $priority = 4;
                }
            } elseif ($order->stages->isNotEmpty() && $incompleteStages->isEmpty()) {
                $statusType = 'completed';
                $statusText = 'مراحل ساخت تکمیل شده';
                $priority = 5;
            } else {
                $statusType = 'upcoming';
                $statusText = 'در انتظار تعیین موعد';
                $priority = 4;
            }

            // Check if log was recorded on the selected target date
            $targetDateLog = $order->dailyLogs->first(function ($l) use ($selectedCarbon) {
                return $l->log_date && $l->log_date->isSameDay($selectedCarbon);
            });
            $latestLog = $order->dailyLogs->sortByDesc('created_at')->first();

            $isLoggedToday = (bool) $targetDateLog;
            if ($isLoggedToday && !$isOverdue && !$isDueOnDate) {
                $priority = 3;
                $statusType = 'completed_today';
                $statusText = 'پیگیری انجام شد';
            }

            return (object) [
                'order'             => $order,
                'currentStage'      => $currentStage,
                'stageDaysStatus'   => [
                    'type' => $statusType,
                    'days' => $diffDays,
                    'text' => $statusText,
                ],
                'targetDateLog'     => $targetDateLog,
                'latestLog'         => $latestLog,
                'isOverdue'         => $isOverdue,
                'isDueOnDate'       => $isDueOnDate,
                'isLoggedToday'     => $isLoggedToday,
                'priority'          => $priority,
                'isDailyActionable' => ($isOverdue || $isDueOnDate || $isLoggedToday),
            ];
        });

        // Calculate KPI Counts strictly connected to the selected date
        $overdueCount = $allRows->where('isOverdue', true)->count();
        $dueTodayCount = $allRows->where('isDueOnDate', true)->count();
        $completedTodayCount = $allRows->where('isLoggedToday', true)->count();
        $dailyTotalCount = $allRows->where('isDailyActionable', true)->count();
        $upcomingCount = $allRows->where('stageDaysStatus.type', 'upcoming')->count();
        $allActiveCount = $allRows->count();

        // Get filter options (distinct lab partners and doctors)
        $labPartners = BookingLaboratoryOrder::whereNotNull('lab_partner_name')
            ->where('lab_partner_name', '!=', '')
            ->distinct()
            ->pluck('lab_partner_name')
            ->toArray();

        $doctors = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['doctor', 'provider', 'dentist', 'admin', 'super-admin']);
        })->get();

        if ($doctors->isEmpty()) {
            $doctors = User::orderBy('name')->take(30)->get();
        }

        // Filter Rows based on active KPI tab
        $rows = $allRows;

        if ($this->kpiFilter === 'daily_all') {
            // Default unified board: show all actionable items for this date (Overdue + Due Today + Completed Today)
            $rows = $allRows->where('isDailyActionable', true);
        } elseif ($this->kpiFilter === 'overdue') {
            $rows = $allRows->where('isOverdue', true);
        } elseif ($this->kpiFilter === 'due_today') {
            $rows = $allRows->where('isDueOnDate', true);
        } elseif ($this->kpiFilter === 'completed_today') {
            $rows = $allRows->where('isLoggedToday', true);
        } elseif ($this->kpiFilter === 'upcoming') {
            $rows = $allRows->where('stageDaysStatus.type', 'upcoming');
        } elseif ($this->kpiFilter === 'all') {
            $rows = $allRows;
        }

        // Apply Search Filter
        if (!empty($this->search)) {
            $s = trim($this->search);
            $rows = $rows->filter(function ($item) use ($s) {
                $ord = $item->order;
                return str_contains($ord->patient_name ?? '', $s) ||
                       str_contains($ord->patient_file_number ?? '', $s) ||
                       str_contains($ord->order_number ?? '', $s) ||
                       str_contains($ord->lab_partner_name ?? '', $s);
            });
        }

        // Apply Lab Partner Filter
        if (!empty($this->selectedLabPartner)) {
            $rows = $rows->filter(function ($item) {
                return $item->order->lab_partner_name === $this->selectedLabPartner;
            });
        }

        // Apply Doctor Filter
        if (!empty($this->selectedDoctorId)) {
            $rows = $rows->filter(function ($item) {
                return (string)$item->order->doctor_id === (string)$this->selectedDoctorId;
            });
        }

        // Sort by priority (Overdue 1 -> Due Today 2 -> Completed 3 -> Upcoming 4)
        $rows = $rows->sortBy('priority');

        // Load detail order if modal is open
        $detailOrder = null;
        if ($this->showDetailModal && $this->detailOrderId) {
            $detailOrder = BookingLaboratoryOrder::with(['stages.completedBy', 'dailyLogs.operator', 'client', 'doctor', 'technician', 'createdBy'])
                ->find($this->detailOrderId);
        }

        return view('booking::user.laboratory.daily-board', [
            'rows'                => $rows,
            'overdueCount'        => $overdueCount,
            'dueTodayCount'       => $dueTodayCount,
            'completedTodayCount' => $completedTodayCount,
            'dailyTotalCount'     => $dailyTotalCount,
            'upcomingCount'       => $upcomingCount,
            'allActiveCount'      => $allActiveCount,
            'labPartners'         => $labPartners,
            'doctors'             => $doctors,
            'detailOrder'         => $detailOrder,
        ]);
    }
}
