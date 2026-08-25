<?php

namespace Modules\Booking\App\Livewire\User;

use Livewire\Component;
use Livewire\Attributes\On;
use Modules\Booking\Entities\BookingWaitlist;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Entities\BookingForm;
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
    public ?int $durationMinutes = null;
    public string $preferredDateJalali = '';
    public string $notes = '';
    public string $errorMessage = '';
    public bool $lockClient = false;

    // Service custom form state
    public ?array $formSchema = null;
    public ?string $formType = null;
    public ?string $formName = null;
    public array $formResponses = [];

    #[On('open-waitlist-modal')]
    #[On('open-add-to-waitlist')]
    public function openModal($clientId = null): void
    {
        $this->reset([
            'clientId', 'clientSearch', 'serviceId', 'providerId', 'durationMinutes',
            'preferredDateJalali', 'notes', 'errorMessage', 'formSchema', 'formType',
            'formName', 'formResponses'
        ]);

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

        $this->loadServiceForm($serviceId);
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
                $this->loadServiceForm(null);
            }
        }
    }

    protected function loadServiceForm($serviceId): void
    {
        if (!$serviceId) {
            $this->formSchema = null;
            $this->formType = null;
            $this->formName = null;
            $this->formResponses = [];
            return;
        }

        $service = BookingService::with('appointmentForm')->find($serviceId);
        if ($service && $service->appointment_form_id && $service->appointmentForm && $service->appointmentForm->status === BookingForm::STATUS_ACTIVE) {
            $this->formName = $service->appointmentForm->name;
            $this->formType = $service->appointmentForm->form_type;
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

            $this->formSchema = $schema;

            // Initialize responses
            $this->formResponses = [];
            foreach ($schema['fields'] ?? [] as $field) {
                $fName = $field['name'] ?? '';
                if ($fName) {
                    if (in_array($field['type'] ?? '', ['checkbox', 'tooth_number']) || !empty($field['multiple'])) {
                        $this->formResponses[$fName] = [];
                    } else {
                        $this->formResponses[$fName] = '';
                    }
                }
            }
        } else {
            $this->formSchema = null;
            $this->formType = null;
            $this->formName = null;
            $this->formResponses = [];
        }
    }

    public function toggleTooth(string $fieldName, int $toothId): void
    {
        if (!isset($this->formResponses[$fieldName]) || !is_array($this->formResponses[$fieldName])) {
            $this->formResponses[$fieldName] = [];
        }
        $key = array_search($toothId, $this->formResponses[$fieldName]);
        if ($key !== false) {
            unset($this->formResponses[$fieldName][$key]);
            $this->formResponses[$fieldName] = array_values($this->formResponses[$fieldName]);
        } else {
            $this->formResponses[$fieldName][] = $toothId;
        }
    }

    public function selectJaw(string $fieldName, string $jaw): void
    {
        $upperJaw = [1,2,3,4,5,6,7,8,9,10,11,12,13,14];
        $lowerJaw = [15,16,17,18,19,20,21,22,23,24,25,26,27,28];
        $this->formResponses[$fieldName] = $jaw === 'upper' ? $upperJaw : $lowerJaw;
    }

    public function selectAllTeeth(string $fieldName): void
    {
        $this->formResponses[$fieldName] = range(1, 28);
    }

    public function resetTeeth(string $fieldName): void
    {
        $this->formResponses[$fieldName] = [];
    }

    public function save(): void
    {
        $this->errorMessage = '';

        if (!$this->clientId) {
            $this->errorMessage = 'لطفاً یک ' . config('clients.labels.singular', 'مراجع') . ' را انتخاب کنید.';
            return;
        }

        // Validate service required fields
        if ($this->formSchema && !empty($this->formSchema['fields'])) {
            foreach ($this->formSchema['fields'] as $field) {
                $fName = $field['name'] ?? '';
                $fLabel = $field['label'] ?? $fName;
                $isRequired = !empty($field['required']);
                if ($isRequired && $fName) {
                    $val = $this->formResponses[$fName] ?? null;
                    $isEmpty = is_null($val) || $val === '' || (is_array($val) && empty($val));
                    if ($isEmpty) {
                        $this->errorMessage = sprintf('تکمیل فیلد «%s» در فرم اختصاصی الزامی است.', $fLabel);
                        return;
                    }
                }
            }
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
            'client_id'                      => $this->clientId,
            'service_id'                     => $this->serviceId ? (int)$this->serviceId : null,
            'provider_user_id'               => $this->providerId ? (int)$this->providerId : null,
            'preferred_date'                 => $prefDateGregorian,
            'duration_minutes'               => $this->durationMinutes ? (int)$this->durationMinutes : null,
            'notes'                          => $this->notes,
            'appointment_form_response_json' => !empty($this->formResponses) ? $this->formResponses : null,
            'status'                         => BookingWaitlist::STATUS_WAITING,
            'created_by_user_id'             => Auth::id(),
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

        $servicesData = BookingService::where('status', BookingService::STATUS_ACTIVE)
            ->with(['providers' => function ($q) {
                $q->where('booking_service_providers.is_active', true);
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($s) {
                return [
                    'id'                  => (int)$s->id,
                    'name'                => (string)$s->name,
                    'appointment_form_id' => $s->appointment_form_id ? (int)$s->appointment_form_id : null,
                    'provider_ids'        => $s->providers->pluck('id')->map(fn($v) => (int)$v)->values()->toArray(),
                ];
            })->values()->toArray();

        $providersData = User::query()
            ->whereIn('id', function ($q) {
                $q->select('provider_user_id')->from('booking_service_providers')->where('is_active', true);
            })
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function ($p) {
                $serviceIds = DB::table('booking_service_providers')
                    ->where('provider_user_id', $p->id)
                    ->where('is_active', true)
                    ->pluck('service_id')
                    ->map(fn($v) => (int)$v)
                    ->values()
                    ->toArray();
                return [
                    'id'          => (int)$p->id,
                    'name'        => (string)$p->name,
                    'service_ids' => $serviceIds,
                ];
            })->values()->toArray();

        if (empty($providersData)) {
            $providersData = User::orderBy('name')->limit(50)->get(['id', 'name'])->map(function ($p) {
                return [
                    'id'          => (int)$p->id,
                    'name'        => (string)$p->name,
                    'service_ids' => [],
                ];
            })->values()->toArray();
        }

        return view('booking::livewire.user.booking-waitlist-modal', [
            'selectedClient' => $selectedClient,
            'clients'        => $clients,
            'servicesData'   => $servicesData,
            'providersData'  => $providersData,
        ]);
    }
}
