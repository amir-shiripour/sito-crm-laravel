<?php

namespace Modules\Booking\App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Modules\Booking\Entities\BookingLaboratoryOrder;
use Modules\Booking\Entities\BookingLaboratoryStage;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Services\LaboratoryService;
use Modules\Clients\Entities\Client;
use App\Models\User;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

#[Layout('layouts.user')]
class LaboratoryManager extends Component
{
    use WithPagination;

    // Filters & Navigation
    public string $activeTab = 'external'; // 'external', 'in_house', 'daily_board', 'all', 'received'
    public string $search = '';
    public string $statusFilter = 'all'; // 'all', 'overdue', 'in_progress', 'received'
    public string $categoryFilter = 'all';
    public ?string $labPartnerFilter = 'all';

    // Order Creation / Edit Modal
    public bool $showCreateModal = false;
    public ?int $editingOrderId = null;
    public string $orderNumber = '';
    public ?int $selectedClientId = null;
    public ?array $selectedClient = null;
    public string $clientSearchQuery = '';
    public string $patientName = '';
    public string $patientFileNumber = '';
    public ?int $selectedDoctorId = null;
    public ?int $selectedTechnicianId = null;
    public string $labType = BookingLaboratoryOrder::LAB_TYPE_EXTERNAL;
    public string $labPartnerName = 'آرمان سلامت';
    public string $categoryType = BookingLaboratoryOrder::CATEGORY_UNITS_1_2;
    public string $fullJawPhase = BookingLaboratoryOrder::FULL_JAW_BASE_RIM;
    public bool $hasPmma = false;
    public int $unitsCount = 1;
    public string $teethNumbers = '';
    public string $sentAtJalali = '';
    public string $orderNotes = '';

    // Quick Stage Completion Modal
    public bool $showStageModal = false;
    public ?int $activeStageId = null;
    public string $stageNote = '';

    // Quick Receive Modal
    public bool $showReceiveModal = false;
    public ?int $activeOrderId = null;
    public string $receiveNote = '';

    // Order Detail Modal
    public bool $showDetailModal = false;
    public ?int $detailOrderId = null;
    public string $detailModalTab = 'overview'; // 'overview', 'dental_chart', 'logs'

    // Notifications
    public ?string $toastSuccess = null;
    public ?string $toastError = null;

    protected $queryString = [
        'activeTab'      => ['except' => 'external'],
        'search'         => ['except' => ''],
        'statusFilter'   => ['except' => 'all'],
        'categoryFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        $settings = BookingSetting::current();
        $this->labPartnerName = $settings->laboratory_default_partner ?: 'آرمان سلامت';
        $this->sentAtJalali = Jalalian::now()->format('Y/m/d');

        $clientId = request()->query('client_id');
        $action = request()->query('action');
        $teeth = request()->query('teeth');
        $doctorId = request()->query('doctor_id');

        if ($clientId) {
            $client = Client::find($clientId);
            if ($client) {
                $this->openCreateModal();
                $this->selectClient($client->id);
                if ($teeth) {
                    $this->teethNumbers = $teeth;
                    $teethList = array_filter(array_map('trim', explode(',', $teeth)));
                    if (count($teethList) > 0) {
                        $this->unitsCount = count($teethList);
                    }
                }
                if ($doctorId) {
                    $this->selectedDoctorId = (int) $doctorId;
                }
            }
        } elseif ($action === 'create') {
            $this->openCreateModal();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActiveTab(): void
    {
        $this->resetPage();
    }

    public function updatedLabType($value): void
    {
        $categories = BookingSetting::getLaboratoryCategoriesForType($value);
        if (!empty($categories)) {
            $this->categoryType = $categories[0]['id'] ?? '';
        }

        $types = BookingSetting::getLaboratoryTypesList();
        foreach ($types as $t) {
            if ($t['id'] === $value && !empty($t['default_partner'])) {
                $this->labPartnerName = $t['default_partner'];
                break;
            }
        }
    }

    public function openCreateModal(): void
    {
        $settings = BookingSetting::current();
        $labService = app(LaboratoryService::class);

        $this->editingOrderId = null;
        $this->orderNumber = $labService->generateOrderNumber();
        $this->selectedClientId = null;
        $this->selectedClient = null;
        $this->clientSearchQuery = '';
        $this->patientName = '';
        $this->patientFileNumber = '';
        $this->selectedDoctorId = auth()->id();
        $this->selectedTechnicianId = null;

        $availableTypes = BookingSetting::getLaboratoryTypesList();
        $defaultTypeId = ($this->activeTab === 'in_house') ? 'in_house' : ($availableTypes[0]['id'] ?? 'external');
        $this->labType = $defaultTypeId;

        $categories = BookingSetting::getLaboratoryCategoriesForType($this->labType);
        $this->categoryType = $categories[0]['id'] ?? 'units_1_2';

        $typeObj = collect($availableTypes)->firstWhere('id', $this->labType);
        $this->labPartnerName = $typeObj['default_partner'] ?? ($settings->laboratory_default_partner ?: 'آرمان سلامت');

        $this->fullJawPhase = BookingLaboratoryOrder::FULL_JAW_BASE_RIM;
        $this->hasPmma = false;
        $this->unitsCount = 1;
        $this->teethNumbers = '';
        $this->sentAtJalali = Jalalian::now()->format('Y/m/d');
        $this->orderNotes = '';

        $this->showCreateModal = true;
    }

    public function openEditModal(int $orderId): void
    {
        $order = BookingLaboratoryOrder::with('client')->find($orderId);
        if (!$order) {
            $this->toastError = 'سفارش مورد نظر یافت نشد.';
            return;
        }

        $this->editingOrderId = $order->id;
        $this->orderNumber = $order->order_number;
        $this->selectedClientId = $order->client_id;
        if ($order->client) {
            $this->selectedClient = [
                'id'            => $order->client->id,
                'full_name'     => $order->client->full_name,
                'phone'         => $order->client->phone,
                'national_code' => $order->client->national_code,
                'case_number'   => $order->client->case_number,
            ];
        } else {
            $this->selectedClient = null;
        }

        $this->clientSearchQuery = '';
        $this->patientName = $order->patient_name;
        $this->patientFileNumber = $order->patient_file_number ?? '';
        $this->selectedDoctorId = $order->doctor_id;
        $this->selectedTechnicianId = $order->technician_id;
        $this->labType = $order->lab_type;
        $this->labPartnerName = $order->lab_partner_name;
        $this->categoryType = $order->category_type;
        $this->fullJawPhase = $order->full_jaw_phase ?: BookingLaboratoryOrder::FULL_JAW_BASE_RIM;
        $this->hasPmma = (bool) $order->has_pmma;
        $this->unitsCount = $order->units_count;
        $this->teethNumbers = $order->teeth_numbers ?? '';
        $this->sentAtJalali = $order->sent_at_jalali ?? ($order->sent_at ? Jalalian::fromCarbon($order->sent_at)->format('Y/m/d') : Jalalian::now()->format('Y/m/d'));
        $this->orderNotes = $order->notes ?? '';

        $this->showCreateModal = true;
    }

    public function selectClient(int $id): void
    {
        $client = Client::find($id);
        if (!$client) return;

        $this->selectedClientId = $client->id;
        $this->patientName = $client->full_name;
        $this->patientFileNumber = $client->case_number ?: ($client->phone ?: '');
        $this->selectedClient = [
            'id'            => $client->id,
            'full_name'     => $client->full_name,
            'phone'         => $client->phone,
            'national_code' => $client->national_code,
            'case_number'   => $client->case_number,
        ];
        $this->clientSearchQuery = '';
    }

    public function clearSelectedClient(): void
    {
        $this->selectedClientId = null;
        $this->selectedClient = null;
        $this->clientSearchQuery = '';
    }

    public function saveOrder(): void
    {
        $this->validate([
            'patientName'   => ['required', 'string', 'max:255'],
            'sentAtJalali'  => ['required', 'string'],
            'labType'       => ['required', 'string'],
            'categoryType'  => ['required', 'string'],
        ], [
            'patientName.required'  => 'نام بیمار الزامی است.',
            'sentAtJalali.required' => 'تاریخ ارسال الزامی است.',
            'labType.required'      => 'انتخاب نوع لابراتوار الزامی است.',
            'categoryType.required' => 'انتخاب دسته‌بندی سفارش الزامی است.',
        ]);

        try {
            // Convert Jalali sent date to Gregorian Carbon
            $carbonSentAt = now();
            if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', trim($this->sentAtJalali), $m)) {
                $j = new Jalalian((int)$m[1], (int)$m[2], (int)$m[3]);
                $carbonSentAt = $j->toCarbon()->setTime(now()->hour, now()->minute, now()->second);
            }

            $service = app(LaboratoryService::class);
            $orderData = [
                'order_number'        => $this->orderNumber,
                'client_id'           => $this->selectedClientId,
                'patient_name'        => trim($this->patientName),
                'patient_file_number' => trim($this->patientFileNumber) ?: null,
                'doctor_id'           => $this->selectedDoctorId,
                'technician_id'       => $this->selectedTechnicianId,
                'lab_type'            => $this->labType,
                'lab_partner_name'    => trim($this->labPartnerName),
                'category_type'       => $this->categoryType,
                'full_jaw_phase'      => ($this->categoryType === BookingLaboratoryOrder::CATEGORY_FULL_JAW) ? $this->fullJawPhase : null,
                'has_pmma'            => $this->hasPmma,
                'units_count'         => max(1, (int)$this->unitsCount),
                'teeth_numbers'       => trim($this->teethNumbers) ?: null,
                'sent_at'             => $carbonSentAt,
                'notes'               => trim($this->orderNotes) ?: null,
            ];

            if ($this->editingOrderId) {
                $existingOrder = BookingLaboratoryOrder::find($this->editingOrderId);
                if (!$existingOrder) {
                    $this->toastError = 'سفارش جهت ویرایش یافت نشد.';
                    return;
                }

                $service->updateOrder($existingOrder, $orderData);
                $this->toastSuccess = 'مشخصات سفارش لابراتوار با موفقیت به‌روزرسانی شد.';
            } else {
                $service->createOrder($orderData);
                $this->toastSuccess = 'سفارش لابراتوار با موفقیت ثبت شد و مراحل پیگیری محاسبه گردید.';
            }

            $this->showCreateModal = false;
            $this->editingOrderId = null;
        } catch (\Throwable $e) {
            $this->toastError = 'خطا در ثبت یا ویرایش سفارش: ' . $e->getMessage();
        }
    }

    public function openStageModal(int $stageId): void
    {
        $this->activeStageId = $stageId;
        $this->stageNote = '';
        $this->showStageModal = true;
    }

    public function confirmStageCompletion(): void
    {
        if (!$this->activeStageId) return;

        $stage = BookingLaboratoryStage::with('order')->find($this->activeStageId);
        if ($stage) {
            $service = app(LaboratoryService::class);
            $service->completeStage($stage, trim($this->stageNote) ?: null, auth()->id());
            $this->toastSuccess = "مرحله {$stage->stage_title} با موفقیت تایید و تکمیل شد.";
        }

        $this->showStageModal = false;
        $this->activeStageId = null;
    }

    public function openReceiveModal(int $orderId): void
    {
        $this->activeOrderId = $orderId;
        $this->receiveNote = '';
        $this->showReceiveModal = true;
    }

    public function confirmReceiveOrder(): void
    {
        if (!$this->activeOrderId) return;

        $order = BookingLaboratoryOrder::with('stages')->find($this->activeOrderId);
        if ($order) {
            $service = app(LaboratoryService::class);
            $service->markOrderReceived($order, trim($this->receiveNote) ?: null, auth()->id());
            $this->toastSuccess = "سفارش بیمار {$order->patient_name} تحویل مطب شد و وضعیت آن سبز گردید.";
        }

        $this->showReceiveModal = false;
        $this->activeOrderId = null;
    }

    public function deleteOrder(int $orderId): void
    {
        $order = BookingLaboratoryOrder::find($orderId);
        if ($order) {
            $order->delete();
            $this->toastSuccess = 'سفارش لابراتوار حذف شد.';
        }
    }

    public function openDetailModal(int $orderId, string $tab = 'overview'): void
    {
        $this->detailOrderId = $orderId;
        $this->detailModalTab = $tab;
        $this->showDetailModal = true;
    }

    public function setDetailTab(string $tab): void
    {
        $this->detailModalTab = $tab;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailOrderId = null;
        $this->detailModalTab = 'overview';
    }

    public function render()
    {
        $query = BookingLaboratoryOrder::with(['stages', 'client', 'doctor', 'technician']);

        // Search
        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('patient_name', 'like', "%{$s}%")
                  ->orWhere('patient_file_number', 'like', "%{$s}%")
                  ->orWhere('order_number', 'like', "%{$s}%")
                  ->orWhere('lab_partner_name', 'like', "%{$s}%");
            });
        }

        // Tab filtering
        if ($this->activeTab === 'received') {
            $query->where('status', BookingLaboratoryOrder::STATUS_RECEIVED);
        } elseif ($this->activeTab === 'all') {
            // No lab_type filter
        } else {
            // Filter by specific lab_type (external, in_house, or any dynamic type)
            $query->where('lab_type', $this->activeTab)
                  ->where('status', '!=', BookingLaboratoryOrder::STATUS_RECEIVED);
        }

        // Category filter
        if ($this->categoryFilter !== 'all') {
            $query->where('category_type', $this->categoryFilter);
        }

        // Status filter
        if ($this->statusFilter === 'received') {
            $query->where('status', BookingLaboratoryOrder::STATUS_RECEIVED);
        } elseif ($this->statusFilter === 'in_progress') {
            $query->where('status', BookingLaboratoryOrder::STATUS_IN_PROGRESS);
        }

        $orders = $query->orderByDesc('id')->paginate(15);

        // Detail order if modal is active
        $detailOrder = null;
        if ($this->showDetailModal && $this->detailOrderId) {
            $detailOrder = BookingLaboratoryOrder::with([
                'stages', 
                'dailyLogs.operator', 
                'client', 
                'doctor', 
                'technician', 
                'createdBy'
            ])->find($this->detailOrderId);
        }

        // Client search results for modal (search by national_code, full_name, phone, case_number)
        $clientResults = [];
        $queryTerm = trim($this->clientSearchQuery);
        if (mb_strlen($queryTerm) >= 2 && !$this->selectedClientId) {
            $clientResults = Client::query()
                ->where(function ($q) use ($queryTerm) {
                    $q->where('full_name', 'like', "%{$queryTerm}%")
                      ->orWhere('national_code', 'like', "%{$queryTerm}%")
                      ->orWhere('phone', 'like', "%{$queryTerm}%")
                      ->orWhere('case_number', 'like', "%{$queryTerm}%");
                })
                ->limit(8)
                ->get(['id', 'full_name', 'phone', 'national_code', 'case_number']);
        }

        $doctors = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['doctor', 'provider', 'dentist', 'admin', 'super-admin']);
        })->get(['id', 'name']);

        $technicians = User::get(['id', 'name']);

        $labTypes = BookingSetting::getLaboratoryTypesList();
        $availableCategories = BookingSetting::getLaboratoryCategoriesForType($this->labType);
        $allCategories = [];
        foreach ($labTypes as $lt) {
            foreach (BookingSetting::getLaboratoryCategoriesForType($lt['id']) as $c) {
                $allCategories[$c['id']] = $c['name'] ?? $c['id'];
            }
        }

        return view('booking::user.laboratory.index', compact(
            'orders', 'detailOrder', 'clientResults', 'doctors', 'technicians', 'labTypes', 'availableCategories', 'allCategories'
        ));
    }
}
