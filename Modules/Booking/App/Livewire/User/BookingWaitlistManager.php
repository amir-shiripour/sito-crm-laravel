<?php

namespace Modules\Booking\App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Modules\Booking\Entities\BookingWaitlist;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingSetting;
use Modules\Clients\Entities\Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

#[Layout('layouts.user')]
class BookingWaitlistManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $selectedServiceFilter = ''; // '' = all, 'general' = general queue, int = service_id
    public ?int $selectedProviderFilter = null;
    public string $statusFilter = 'waiting'; // 'waiting', 'notified', 'in_progress', 'converted', 'canceled', 'all'

    // Create / Edit Modal State
    public bool $showCreateModal = false;
    public bool $isEditing = false;
    public ?int $modalClientId = null;
    public string $modalClientSearch = '';
    public ?int $modalServiceId = null;
    public ?int $modalProviderId = null;
    public ?int $modalDurationMinutes = null;
    public string $modalPreferredDateJalali = '';
    public string $modalNotes = '';
    public string $modalStatus = BookingWaitlist::STATUS_WAITING;
    public string $modalError = '';

    // Service Custom Form in Modal
    public ?array $modalFormSchema = null;
    public ?string $modalFormType = null;
    public ?string $modalFormName = null;
    public array $modalFormResponses = [];

    // Quick Status Update Modal State
    public bool $showStatusModal = false;
    public ?int $editingEntryId = null;
    public string $editingStatus = '';
    public string $editingNotes = '';

    // Alert Messages
    public ?string $toastSuccess = null;
    public ?string $toastError = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedServiceFilter' => ['except' => ''],
        'selectedProviderFilter' => ['except' => null],
        'statusFilter' => ['except' => 'waiting'],
    ];

    public function mount(): void
    {
        if (request()->has('create_for_client')) {
            $clientId = (int) request('create_for_client');
            $client = Client::find($clientId);
            if ($client) {
                $this->modalClientId = $client->id;
                $this->showCreateModal = true;
            }
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedServiceFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedProviderFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedModalServiceId($serviceId): void
    {
        if ($serviceId && $this->modalProviderId) {
            $isValid = \Illuminate\Support\Facades\DB::table('booking_service_providers')
                ->where('service_id', $serviceId)
                ->where('provider_user_id', $this->modalProviderId)
                ->where('is_active', true)
                ->exists();
            if (!$isValid) {
                $this->modalProviderId = null;
            }
        }

        $this->loadModalServiceForm($serviceId);
    }

    public function updatedModalProviderId($providerId): void
    {
        if ($providerId && $this->modalServiceId) {
            $isValid = \Illuminate\Support\Facades\DB::table('booking_service_providers')
                ->where('service_id', $this->modalServiceId)
                ->where('provider_user_id', $providerId)
                ->where('is_active', true)
                ->exists();
            if (!$isValid) {
                $this->modalServiceId = null;
                $this->loadModalServiceForm(null);
            }
        }
    }

    protected function loadModalServiceForm($serviceId): void
    {
        if (!$serviceId) {
            $this->modalFormSchema = null;
            $this->modalFormType = null;
            $this->modalFormName = null;
            $this->modalFormResponses = [];
            return;
        }

        $service = BookingService::with('appointmentForm')->find($serviceId);
        if ($service && $service->appointment_form_id && $service->appointmentForm && $service->appointmentForm->status === \Modules\Booking\Entities\BookingForm::STATUS_ACTIVE) {
            $this->modalFormName = $service->appointmentForm->name;
            $this->modalFormType = $service->appointmentForm->form_type;
            $schema = $service->appointmentForm->schema_json ?? [];

            if (isset($schema['fields']) && is_array($schema['fields'])) {
                foreach ($schema['fields'] as &$field) {
                    if (($field['type'] ?? '') === 'select-user-by-role') {
                        $roleName = $field['role'] ?? null;
                        $usersQ = User::query();
                        if ($roleName) {
                            $usersQ->whereHas('roles', fn($r) => $r->where('name', $roleName));
                        }
                        $field['user_options'] = $usersQ->orderBy('name')->get(['id', 'name'])->toArray();
                    }
                }
            }

            $this->modalFormSchema = $schema;

            $this->modalFormResponses = [];
            foreach ($schema['fields'] ?? [] as $field) {
                $fName = $field['name'] ?? '';
                if ($fName) {
                    if (in_array($field['type'] ?? '', ['checkbox', 'tooth_number']) || !empty($field['multiple'])) {
                        $this->modalFormResponses[$fName] = [];
                    } else {
                        $this->modalFormResponses[$fName] = '';
                    }
                }
            }
        } else {
            $this->modalFormSchema = null;
            $this->modalFormType = null;
            $this->modalFormName = null;
            $this->modalFormResponses = [];
        }
    }

    public function openCreateModal(): void
    {
        $this->isEditing = false;
        $this->editingEntryId = null;
        $this->modalStatus = BookingWaitlist::STATUS_WAITING;
        $this->reset([
            'modalClientId', 'modalClientSearch', 'modalServiceId', 'modalProviderId',
            'modalDurationMinutes', 'modalPreferredDateJalali', 'modalNotes', 'modalError',
            'modalFormSchema', 'modalFormType', 'modalFormName', 'modalFormResponses'
        ]);
        $this->showCreateModal = true;
    }

    public function openEditModal(int $id): void
    {
        $entry = BookingWaitlist::find($id);
        if (!$entry) {
            $this->toastError = 'آیتم مورد نظر در صف انتظار یافت نشد.';
            return;
        }

        $this->isEditing = true;
        $this->editingEntryId = $entry->id;
        $this->modalClientId = $entry->client_id;
        $this->modalClientSearch = '';
        $this->modalServiceId = $entry->service_id;
        $this->modalProviderId = $entry->provider_user_id;
        $this->modalDurationMinutes = $entry->duration_minutes;
        $this->modalNotes = $entry->notes ?? '';
        $this->modalStatus = $entry->status ?? BookingWaitlist::STATUS_WAITING;
        $this->modalError = '';

        if ($entry->preferred_date) {
            try {
                $this->modalPreferredDateJalali = Jalalian::fromDateTime($entry->preferred_date)->format('Y/m/d');
            } catch (\Throwable $e) {
                $this->modalPreferredDateJalali = '';
            }
        } else {
            $this->modalPreferredDateJalali = '';
        }

        $this->loadModalServiceForm($entry->service_id);

        if (!empty($entry->appointment_form_response_json) && is_array($entry->appointment_form_response_json)) {
            foreach ($entry->appointment_form_response_json as $key => $val) {
                $this->modalFormResponses[$key] = $val;
            }
        }

        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->isEditing = false;
        $this->editingEntryId = null;
        $this->modalError = '';
    }

    public function selectModalClient(int $clientId): void
    {
        $this->modalClientId = $clientId;
        $this->modalClientSearch = '';
    }

    public function saveNewWaitlistEntry(): void
    {
        $this->saveWaitlistEntry();
    }

    public function saveWaitlistEntry(): void
    {
        $this->modalError = '';

        if (!$this->modalClientId) {
            $this->modalError = 'لطفاً یک مراجع/بیمار را انتخاب کنید.';
            return;
        }

        // Validate required fields in service form
        if ($this->modalFormSchema && !empty($this->modalFormSchema['fields'])) {
            foreach ($this->modalFormSchema['fields'] as $field) {
                $fName = $field['name'] ?? '';
                $fLabel = $field['label'] ?? $fName;
                $isRequired = !empty($field['required']);
                if ($isRequired && $fName) {
                    $val = $this->modalFormResponses[$fName] ?? null;
                    $isEmpty = is_null($val) || $val === '' || (is_array($val) && empty($val));
                    if ($isEmpty) {
                        $this->modalError = sprintf('تکمیل فیلد «%s» در فرم اختصاصی الزامی است.', $fLabel);
                        return;
                    }
                }
            }
        }

        $prefDateGregorian = null;
        if (!empty($this->modalPreferredDateJalali)) {
            try {
                $j = Jalalian::fromFormat('Y/m/d', $this->modalPreferredDateJalali);
                $prefDateGregorian = $j->toCarbon()->toDateString();
            } catch (\Throwable $e) {
                $prefDateGregorian = null;
            }
        }

        if ($this->isEditing && $this->editingEntryId) {
            $entry = BookingWaitlist::find($this->editingEntryId);
            if (!$entry) {
                $this->modalError = 'نوبت مورد نظر یافت نشد.';
                return;
            }

            $entry->update([
                'client_id'                      => $this->modalClientId,
                'service_id'                     => $this->modalServiceId ? (int)$this->modalServiceId : null,
                'provider_user_id'               => $this->modalProviderId ? (int)$this->modalProviderId : null,
                'preferred_date'                 => $prefDateGregorian,
                'duration_minutes'               => $this->modalDurationMinutes ? (int)$this->modalDurationMinutes : null,
                'notes'                          => $this->modalNotes,
                'appointment_form_response_json' => !empty($this->modalFormResponses) ? $this->modalFormResponses : null,
                'status'                         => $this->modalStatus,
            ]);

            if ($this->modalStatus === BookingWaitlist::STATUS_NOTIFIED && !$entry->notified_at) {
                $entry->update(['notified_at' => now()]);
            }

            $this->closeCreateModal();
            $this->toastSuccess = 'اطلاعات صف انتظار با موفقیت ویرایش شد.';
        } else {
            $settings = BookingSetting::current();
            if ($settings->queue_max_size && $settings->queue_max_size > 0) {
                $currentWaitingCount = BookingWaitlist::where('status', BookingWaitlist::STATUS_WAITING)->count();
                if ($currentWaitingCount >= $settings->queue_max_size) {
                    $this->modalError = sprintf('ظرفیت صف انتظار تکمیل است (حداکثر %d نفر).', $settings->queue_max_size);
                    return;
                }
            }

            BookingWaitlist::create([
                'client_id'                      => $this->modalClientId,
                'service_id'                     => $this->modalServiceId ? (int)$this->modalServiceId : null,
                'provider_user_id'               => $this->modalProviderId ? (int)$this->modalProviderId : null,
                'preferred_date'                 => $prefDateGregorian,
                'duration_minutes'               => $this->modalDurationMinutes ? (int)$this->modalDurationMinutes : null,
                'notes'                          => $this->modalNotes,
                'appointment_form_response_json' => !empty($this->modalFormResponses) ? $this->modalFormResponses : null,
                'status'                         => BookingWaitlist::STATUS_WAITING,
                'created_by_user_id'             => Auth::id(),
            ]);

            $this->closeCreateModal();
            $this->toastSuccess = 'مراجع با موفقیت در صف انتظار قرار گرفت.';
        }
    }

    public function toggleTooth(string $fieldName, int $toothId): void
    {
        if (!isset($this->modalFormResponses[$fieldName]) || !is_array($this->modalFormResponses[$fieldName])) {
            $this->modalFormResponses[$fieldName] = [];
        }
        $key = array_search($toothId, $this->modalFormResponses[$fieldName]);
        if ($key !== false) {
            unset($this->modalFormResponses[$fieldName][$key]);
            $this->modalFormResponses[$fieldName] = array_values($this->modalFormResponses[$fieldName]);
        } else {
            $this->modalFormResponses[$fieldName][] = $toothId;
        }
    }

    public function selectJaw(string $fieldName, string $jaw): void
    {
        $upperJaw = [1,2,3,4,5,6,7,8,9,10,11,12,13,14];
        $lowerJaw = [15,16,17,18,19,20,21,22,23,24,25,26,27,28];
        $this->modalFormResponses[$fieldName] = $jaw === 'upper' ? $upperJaw : $lowerJaw;
    }

    public function selectAllTeeth(string $fieldName): void
    {
        $this->modalFormResponses[$fieldName] = range(1, 28);
    }

    public function resetTeeth(string $fieldName): void
    {
        $this->modalFormResponses[$fieldName] = [];
    }

    public function openStatusModal(int $entryId): void
    {
        $entry = BookingWaitlist::find($entryId);
        if ($entry) {
            $this->editingEntryId = $entry->id;
            $this->editingStatus = $entry->status;
            $this->editingNotes = $entry->notes ?? '';
            $this->showStatusModal = true;
        }
    }

    public function closeStatusModal(): void
    {
        $this->showStatusModal = false;
        $this->editingEntryId = null;
    }

    public function updateEntryStatus(): void
    {
        if (!$this->editingEntryId) return;

        $entry = BookingWaitlist::find($this->editingEntryId);
        if ($entry) {
            $oldStatus = $entry->status;
            $entry->status = $this->editingStatus;
            $entry->notes = $this->editingNotes;

            if ($this->editingStatus === BookingWaitlist::STATUS_NOTIFIED && !$entry->notified_at) {
                $entry->notified_at = now();
            }

            $entry->save();

            $this->closeStatusModal();
            $this->toastSuccess = 'وضعیت نوبت در صف با موفقیت به‌روزرسانی شد.';
        }
    }

    public function deleteEntry(int $entryId): void
    {
        $entry = BookingWaitlist::find($entryId);
        if ($entry) {
            $entry->update(['status' => BookingWaitlist::STATUS_CANCELED]);
            $entry->delete();
            $this->toastSuccess = 'مراجع با موفقیت از صف انتظار حذف شد.';
        } else {
            $this->toastError = 'آیتم مورد نظر یافت نشد.';
        }
    }

    public function cancelEntry(int $entryId): void
    {
        $this->deleteEntry($entryId);
    }

    public function render()
    {
        $settings = BookingSetting::current();
        $isQueueEnabled = (bool)($settings->queue_enabled ?? false);

        $query = BookingWaitlist::query()
            ->with(['client', 'service', 'provider', 'creator', 'appointment']);

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->whereHas('client', function ($q) use ($s) {
                $q->where('full_name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('national_code', 'like', "%{$s}%");
            });
        }

        if ($this->selectedServiceFilter === 'general') {
            $query->whereNull('service_id');
        } elseif (!empty($this->selectedServiceFilter) && is_numeric($this->selectedServiceFilter)) {
            $query->where('service_id', (int)$this->selectedServiceFilter);
        }

        if ($this->selectedProviderFilter) {
            $query->where('provider_user_id', $this->selectedProviderFilter);
        }

        if ($this->statusFilter && $this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $entries = $query->orderBy('created_at', 'asc')->orderBy('id', 'asc')->paginate(15);

        // KPI stats
        $totalWaitingCount = BookingWaitlist::where('status', BookingWaitlist::STATUS_WAITING)->count();
        $generalWaitingCount = BookingWaitlist::where('status', BookingWaitlist::STATUS_WAITING)->whereNull('service_id')->count();
        $convertedCount = BookingWaitlist::where('status', BookingWaitlist::STATUS_CONVERTED)->count();
        $todayAddedCount = BookingWaitlist::whereDate('created_at', today())->count();

        // Clients for modal search
        $clientsQuery = Client::query()->visibleForUser(auth()->user());
        if (!empty($this->modalClientSearch) && strlen($this->modalClientSearch) >= 1) {
            $cs = trim($this->modalClientSearch);
            $clientsForModal = $clientsQuery
                ->where(function ($q) use ($cs) {
                    $q->where('full_name', 'like', "%{$cs}%")
                        ->orWhere('phone', 'like', "%{$cs}%")
                        ->orWhere('national_code', 'like', "%{$cs}%")
                        ->orWhere('case_number', 'like', "%{$cs}%");
                })
                ->orderByDesc('id')
                ->limit(10)
                ->get(['id', 'full_name', 'phone', 'national_code', 'case_number']);
        } else {
            $clientsForModal = $clientsQuery->orderByDesc('id')->limit(3)->get(['id', 'full_name', 'phone', 'national_code', 'case_number']);
        }

        $selectedModalClient = $this->modalClientId ? Client::find($this->modalClientId) : null;

        $services = BookingService::where('status', BookingService::STATUS_ACTIVE)->orderBy('name')->get();
        $providers = User::query()
            ->whereIn('id', function ($q) {
                $q->select('provider_user_id')->from('booking_service_providers')->where('is_active', true);
            })
            ->orderBy('name')
            ->get();
        if ($providers->isEmpty()) {
            $providers = User::orderBy('name')->limit(50)->get();
        }

        // Modal filtered lists
        $modalServicesQuery = BookingService::where('status', BookingService::STATUS_ACTIVE)->orderBy('name');
        if ($this->modalProviderId) {
            $modalServicesQuery->whereHas('providers', function ($q) {
                $q->where('users.id', $this->modalProviderId)->where('booking_service_providers.is_active', true);
            });
        }
        $modalServices = $modalServicesQuery->get();

        $modalProvidersQuery = User::query()
            ->whereIn('id', function ($q) {
                $q->select('provider_user_id')->from('booking_service_providers')->where('is_active', true);
            })
            ->orderBy('name');
        if ($this->modalServiceId) {
            $modalProvidersQuery->whereIn('id', function ($q) {
                $q->select('provider_user_id')->from('booking_service_providers')->where('service_id', $this->modalServiceId)->where('is_active', true);
            });
        }
        $modalProviders = $modalProvidersQuery->get();

        return view('booking::livewire.user.booking-waitlist-manager', [
            'entries'              => $entries,
            'isQueueEnabled'       => $isQueueEnabled,
            'totalWaitingCount'    => $totalWaitingCount,
            'generalWaitingCount'  => $generalWaitingCount,
            'convertedCount'       => $convertedCount,
            'todayAddedCount'      => $todayAddedCount,
            'services'             => $services,
            'providers'            => $providers,
            'modalServices'        => $modalServices,
            'modalProviders'       => $modalProviders,
            'clientsForModal'      => $clientsForModal,
            'selectedModalClient'  => $selectedModalClient,
            'statusLabels'         => BookingWaitlist::statusLabels(),
            'statusColors'         => BookingWaitlist::statusColors(),
        ]);
    }
}
