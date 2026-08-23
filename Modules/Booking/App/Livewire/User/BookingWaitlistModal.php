<?php

namespace Modules\Booking\App\Livewire\User;

use Livewire\Component;
use Livewire\Attributes\On;
use Modules\Booking\Entities\BookingWaitlist;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingSetting;
use Modules\Clients\Entities\Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class BookingWaitlistModal extends Component
{
    public bool $isOpen = false;
    public ?int $clientId = null;
    public string $clientSearch = '';
    public ?int $serviceId = null;
    public ?int $providerId = null;
    public string $preferredDateJalali = '';
    public string $notes = '';
    public string $errorMessage = '';
    public bool $lockClient = false;

    #[On('open-waitlist-modal')]
    #[On('open-add-to-waitlist')]
    public function openModal($clientId = null): void
    {
        $this->reset(['clientId', 'clientSearch', 'serviceId', 'providerId', 'preferredDateJalali', 'notes', 'errorMessage']);
        if ($clientId) {
            $this->clientId = (int) $clientId;
            $this->lockClient = true;
        } else {
            $this->lockClient = false;
        }
        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->errorMessage = '';
    }

    public function selectClient(int $clientId): void
    {
        $this->clientId = $clientId;
        $this->clientSearch = '';
    }

    public function updatedServiceId($serviceId): void
    {
        if ($serviceId && $this->providerId) {
            $isValid = DB::table('booking_service_providers')
                ->where('service_id', $serviceId)
                ->where('provider_user_id', $this->providerId)
                ->where('is_active', true)
                ->exists();
            if (!$isValid) {
                $this->providerId = null;
            }
        }
    }

    public function updatedProviderId($providerId): void
    {
        if ($providerId && $this->serviceId) {
            $isValid = DB::table('booking_service_providers')
                ->where('service_id', $this->serviceId)
                ->where('provider_user_id', $providerId)
                ->where('is_active', true)
                ->exists();
            if (!$isValid) {
                $this->serviceId = null;
            }
        }
    }

    public function save(): void
    {
        $this->errorMessage = '';

        if (!$this->clientId) {
            $this->errorMessage = 'لطفاً یک ' . config('clients.labels.singular', 'مراجع') . ' را انتخاب کنید.';
            return;
        }

        $settings = BookingSetting::current();
        if ($settings->queue_max_size && $settings->queue_max_size > 0) {
            $currentWaitingCount = BookingWaitlist::where('status', BookingWaitlist::STATUS_WAITING)->count();
            if ($currentWaitingCount >= $settings->queue_max_size) {
                $this->errorMessage = sprintf('ظرفیت صف انتظار تکمیل است (حداکثر %d نفر).', $settings->queue_max_size);
                return;
            }
        }

        $prefDateGregorian = null;
        if (!empty($this->preferredDateJalali)) {
            try {
                $j = Jalalian::fromFormat('Y/m/d', $this->preferredDateJalali);
                $prefDateGregorian = $j->toCarbon()->toDateString();
            } catch (\Throwable $e) {
                $prefDateGregorian = null;
            }
        }

        BookingWaitlist::create([
            'client_id'          => $this->clientId,
            'service_id'         => $this->serviceId ? (int)$this->serviceId : null,
            'provider_user_id'   => $this->providerId ? (int)$this->providerId : null,
            'preferred_date'     => $prefDateGregorian,
            'notes'              => $this->notes,
            'status'             => BookingWaitlist::STATUS_WAITING,
            'created_by_user_id' => Auth::id(),
        ]);

        $this->closeModal();
        $this->dispatch('notify', type: 'success', text: config('clients.labels.singular', 'مراجع') . ' با موفقیت در صف انتظار قرار گرفت.');
        $this->dispatch('waitlist-entry-created');
        $this->js('if (window.location.pathname.includes("/user/clients")) { setTimeout(() => window.location.reload(), 400); }');
    }

    public function render()
    {
        $selectedClient = null;
        if ($this->clientId) {
            $selectedClient = Client::find($this->clientId);
        }

        $clients = [];
        if (!$this->clientId) {
            $clientQuery = Client::query();
            if (!empty($this->clientSearch)) {
                $s = trim($this->clientSearch);
                $clientQuery->where(function ($q) use ($s) {
                    $q->where('full_name', 'like', "%{$s}%")
                      ->orWhere('phone', 'like', "%{$s}%")
                      ->orWhere('case_number', 'like', "%{$s}%")
                      ->orWhere('national_code', 'like', "%{$s}%");
                })->limit(50);
            } else {
                $clientQuery->latest('id')->limit(3);
            }
            $clients = $clientQuery->get(['id', 'full_name', 'phone', 'national_code', 'case_number']);
        }

        $servicesQuery = BookingService::active()->orderBy('name');
        if ($this->providerId) {
            $servicesQuery->whereHas('providers', function ($q) {
                $q->where('users.id', $this->providerId)->where('booking_service_providers.is_active', true);
            });
        }
        $services = $servicesQuery->get();

        $providersQuery = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['doctor', 'provider', 'specialist', 'operator', 'super-admin']);
        })->orderBy('name');
        if ($this->serviceId) {
            $providersQuery->whereHas('bookingServices', function ($q) {
                $q->where('booking_services.id', $this->serviceId)->where('booking_service_providers.is_active', true);
            });
        }
        $providers = $providersQuery->get(['id', 'name']);

        return view('booking::livewire.user.booking-waitlist-modal', [
            'selectedClient' => $selectedClient,
            'clients' => $clients,
            'services' => $services,
            'providers' => $providers,
        ]);
    }
}
