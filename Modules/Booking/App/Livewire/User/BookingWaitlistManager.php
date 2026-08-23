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

    // Create Modal State
    public bool $showCreateModal = false;
    public ?int $modalClientId = null;
    public string $modalClientSearch = '';
    public ?int $modalServiceId = null;
    public ?int $modalProviderId = null;
    public string $modalPreferredDateJalali = '';
    public string $modalNotes = '';
    public string $modalError = '';

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
            }
        }
    }

    public function openCreateModal(): void
    {
        $this->reset(['modalClientId', 'modalClientSearch', 'modalServiceId', 'modalProviderId', 'modalPreferredDateJalali', 'modalNotes', 'modalError']);
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->modalError = '';
    }

    public function selectModalClient(int $clientId): void
    {
        $this->modalClientId = $clientId;
        $this->modalClientSearch = '';
    }

    public function saveNewWaitlistEntry(): void
    {
        $this->modalError = '';

        if (!$this->modalClientId) {
            $this->modalError = 'لطفاً یک مراجع/بیمار را انتخاب کنید.';
            return;
        }

        $settings = BookingSetting::current();
        if ($settings->queue_max_size && $settings->queue_max_size > 0) {
            $currentWaitingCount = BookingWaitlist::where('status', BookingWaitlist::STATUS_WAITING)->count();
            if ($currentWaitingCount >= $settings->queue_max_size) {
                $this->modalError = sprintf('ظرفیت صف انتظار تکمیل است (حداکثر %d نفر).', $settings->queue_max_size);
                return;
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

        BookingWaitlist::create([
            'client_id'          => $this->modalClientId,
            'service_id'         => $this->modalServiceId ? (int)$this->modalServiceId : null,
            'provider_user_id'   => $this->modalProviderId ? (int)$this->modalProviderId : null,
            'preferred_date'     => $prefDateGregorian,
            'notes'              => $this->modalNotes,
            'status'             => BookingWaitlist::STATUS_WAITING,
            'created_by_user_id' => Auth::id(),
        ]);

        $this->closeCreateModal();
        $this->toastSuccess = 'مراجع با موفقیت در صف انتظار قرار گرفت.';
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

    public function cancelEntry(int $entryId): void
    {
        $entry = BookingWaitlist::find($entryId);
        if ($entry) {
            $entry->update(['status' => BookingWaitlist::STATUS_CANCELED]);
            $entry->delete();
            $this->toastSuccess = 'نوبت با موفقیت از صف انتظار خارج شد.';
        }
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

        $entries = $query->orderBy('position', 'asc')->orderBy('id', 'asc')->paginate(15);

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
