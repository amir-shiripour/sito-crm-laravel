<?php

namespace Modules\FieldService\App\Livewire\Admin;

use App\Helpers\ProvinceCity;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Clients\Entities\Client;
use Modules\Clients\Entities\ClientAddress;
use Modules\FieldService\App\Models\Building;
use Modules\FieldService\App\Models\BuildingEquipment;
use Modules\FieldService\App\Models\SlaContract;
use Illuminate\Support\Str;

class BuildingsManager extends Component
{
    use WithPagination;

    // Filters & Search
    public string $search = '';
    public string $typeFilter = 'all';
    public string $slaFilter = 'all';

    // Create / Edit Modal State
    public bool $showModal = false;
    public ?int $editingBuildingId = null;

    // Client Selection
    public ?int $selectedClientId = null;
    public string $clientSearch = '';
    public bool $isCreatingNewClient = false;
    public string $newClientName = '';
    public string $newClientPhone = '';

    // Building Fields
    public string $name = '';
    public string $building_type = 'residential';
    public int $units_count = 12;
    public int $floors_count = 5;
    public string $manager_name = '';
    public string $manager_phone = '';
    public string $province = 'مازندران';
    public string $city = 'بابل';
    public string $address = '';
    public string $notes = '';

    // Passport Drawer / Modal State
    public bool $showPassportDrawer = false;
    public ?int $activeBuildingId = null;
    public string $passportTab = 'equipments'; // equipments, sla, orders

    // Add Equipment Form inside Passport
    public bool $showAddEquipmentForm = false;
    public string $equipType = 'boiler_room';
    public string $equipTitle = '';
    public string $equipBrandModel = '';
    public string $equipSerialNumber = '';
    public string $equipInstallationYear = '1401';
    public string $equipHealthStatus = 'good';
    public ?string $equipNextServiceDue = null;
    public string $equipNotes = '';

    // Add SLA Contract Form inside Passport
    public bool $showAddSlaForm = false;
    public string $slaContractNumber = '';
    public string $slaTitle = 'قرارداد پایش و نگهداری دوره‌ای تاسیسات';
    public string $slaTier = 'gold_sla';
    public string $slaStartDate = '';
    public string $slaEndDate = '';
    public float $slaMonthlyFee = 3500000;
    public int $slaEmergencyHours = 2;
    public int $slaVisitsPerMonth = 2;
    public string $slaTerms = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedProvince($value)
    {
        $cities = ProvinceCity::getCities($value);
        if ($value === 'مازندران' && in_array('بابل', $cities, true)) {
            $this->city = 'بابل';
        } else {
            $this->city = $cities[0] ?? '';
        }
    }

    public function selectClient(int $clientId)
    {
        $client = Client::find($clientId);
        if ($client) {
            $this->selectedClientId = $client->id;
            $this->manager_name = $client->full_name;
            $this->manager_phone = $client->phone ?? '';
            $this->clientSearch = $client->full_name . ' (' . ($client->phone ?? 'بدون شماره') . ')';

            // Pre-fill address if client has default address
            $defaultAddr = $client->addresses()->where('is_default', true)->first() ?: $client->addresses()->first();
            if ($defaultAddr) {
                if (!empty($defaultAddr->province)) $this->province = $defaultAddr->province;
                if (!empty($defaultAddr->city)) $this->city = $defaultAddr->city;
                if (!empty($defaultAddr->address)) $this->address = $defaultAddr->address;
            }
        }
    }

    public function clearSelectedClient()
    {
        $this->selectedClientId = null;
        $this->clientSearch = '';
    }

    public function openCreateModal()
    {
        $this->reset([
            'editingBuildingId',
            'selectedClientId',
            'clientSearch',
            'isCreatingNewClient',
            'newClientName',
            'newClientPhone',
            'name',
            'manager_name',
            'manager_phone',
            'address',
            'notes',
        ]);
        $this->building_type = 'residential';
        $this->units_count = 12;
        $this->floors_count = 5;
        $this->province = 'مازندران';
        $this->city = 'بابل';
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function editBuilding(int $id)
    {
        $building = Building::with('client')->findOrFail($id);
        $this->editingBuildingId = $building->id;
        $this->name = $building->name;
        $this->building_type = $building->building_type;
        $this->units_count = $building->units_count;
        $this->floors_count = $building->floors_count;
        $this->manager_name = $building->manager_name ?? '';
        $this->manager_phone = $building->manager_phone ?? '';
        $this->province = $building->province ?: 'مازندران';
        $this->city = $building->city ?: 'بابل';
        $this->address = $building->address ?? '';
        $this->notes = $building->notes ?? '';
        $this->selectedClientId = $building->client_id;
        if ($building->client) {
            $this->clientSearch = $building->client->full_name . ' (' . ($building->client->phone ?? '') . ')';
        } else {
            $this->clientSearch = '';
        }
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function saveBuilding()
    {
        $this->validate([
            'name'          => 'required|string|max:150',
            'building_type' => 'required|string|max:40',
            'units_count'   => 'required|integer|min:1',
            'floors_count'  => 'required|integer|min:1',
            'province'      => 'required|string',
            'city'          => 'required|string',
            'address'       => 'required|string|max:500',
        ], [
            'name.required'     => 'نام ساختمان یا مجتمع الزامی است.',
            'province.required' => 'انتخاب استان الزامی است.',
            'city.required'     => 'انتخاب شهر الزامی است.',
            'address.required'  => 'آدرس دقیق مجتمع الزامی است.',
        ]);

        // Resolve or Create Client
        $clientId = $this->selectedClientId;
        if (!$clientId) {
            $phone = trim($this->isCreatingNewClient ? $this->newClientPhone : $this->manager_phone);
            $name  = trim($this->isCreatingNewClient ? $this->newClientName : $this->manager_name);

            if (!empty($phone)) {
                $client = Client::firstOrCreate(
                    ['phone' => $phone],
                    ['full_name' => $name ?: $this->name]
                );
                $clientId = $client->id;
                $this->manager_name = $client->full_name;
                $this->manager_phone = $client->phone;
            } elseif (!empty($name)) {
                $client = Client::create([
                    'full_name' => $name,
                    'phone'     => null,
                ]);
                $clientId = $client->id;
            }
        }

        $payload = [
            'client_id'     => $clientId,
            'name'          => $this->name,
            'building_type' => $this->building_type,
            'units_count'   => $this->units_count,
            'floors_count'  => $this->floors_count,
            'manager_name'  => $this->manager_name,
            'manager_phone' => $this->manager_phone,
            'province'      => $this->province,
            'city'          => $this->city,
            'address'       => $this->address,
            'notes'         => $this->notes,
        ];

        if ($this->editingBuildingId) {
            $building = Building::findOrFail($this->editingBuildingId);
            $building->update($payload);
            $msg = "مشخصات مجتمع {$building->name} با موفقیت ویرایش شد.";
        } else {
            $building = Building::create($payload);
            $msg = "شناسنامه مجتمع {$building->name} با موفقیت در سیستم ثبت گردید.";
        }

        // Sync address to ClientAddress if client exists
        if ($clientId && class_exists(ClientAddress::class)) {
            ClientAddress::firstOrCreate([
                'client_id' => $clientId,
                'title'     => $this->name,
            ], [
                'province'   => $this->province,
                'city'       => $this->city,
                'address'    => $this->address,
                'is_default' => false,
            ]);
        }

        $this->showModal = false;
        $this->dispatch('notify', [
            'type' => 'success',
            'text' => $msg,
        ]);
    }

    public function deleteBuilding(int $id)
    {
        $building = Building::find($id);
        if ($building) {
            $name = $building->name;
            $building->delete();
            $this->dispatch('notify', [
                'type' => 'success',
                'text' => "مجتمع {$name} با موفقیت حذف شد.",
            ]);
        }
    }

    // ================= PASSPORT DRAWER ACTIONS =================
    public function openPassport(int $buildingId)
    {
        $this->activeBuildingId = $buildingId;
        $this->passportTab = 'equipments';
        $this->showAddEquipmentForm = false;
        $this->showAddSlaForm = false;
        $this->showPassportDrawer = true;
    }

    public function closePassport()
    {
        $this->showPassportDrawer = false;
        $this->activeBuildingId = null;
    }

    public function toggleAddEquipment()
    {
        $this->showAddEquipmentForm = !$this->showAddEquipmentForm;
        if ($this->showAddEquipmentForm) {
            $this->reset(['equipTitle', 'equipBrandModel', 'equipSerialNumber', 'equipNotes']);
            $this->equipType = 'boiler_room';
            $this->equipInstallationYear = '1401';
            $this->equipHealthStatus = 'good';
            $this->equipNextServiceDue = now()->addMonths(3)->format('Y-m-d');
        }
    }

    public function saveEquipment()
    {
        $this->validate([
            'equipTitle' => 'required|string|max:120',
            'equipType'  => 'required|string',
        ], [
            'equipTitle.required' => 'عنوان یا شناسه تجهیز الزامی است.',
        ]);

        if ($this->activeBuildingId) {
            BuildingEquipment::create([
                'building_id'       => $this->activeBuildingId,
                'equipment_type'    => $this->equipType,
                'title'             => $this->equipTitle,
                'brand_model'       => $this->equipBrandModel,
                'serial_number'     => $this->equipSerialNumber,
                'installation_year' => $this->equipInstallationYear,
                'health_status'     => $this->equipHealthStatus,
                'last_serviced_at'  => now(),
                'next_service_due'  => $this->equipNextServiceDue ? now()->parse($this->equipNextServiceDue) : now()->addMonths(3),
                'notes'             => $this->equipNotes,
            ]);

            $this->showAddEquipmentForm = false;
            $this->dispatch('notify', [
                'type' => 'success',
                'text' => 'تجهیز جدید با موفقیت به شناسنامه تاسیسات مجتمع اضافه شد.',
            ]);
        }
    }

    public function updateEquipmentHealth(int $equipmentId, string $status)
    {
        $equipment = BuildingEquipment::find($equipmentId);
        if ($equipment) {
            $equipment->update([
                'health_status'    => $status,
                'last_serviced_at' => now(),
            ]);
            $this->dispatch('notify', [
                'type' => 'success',
                'text' => "وضعیت سلامت تجهیز {$equipment->title} به‌روزرسانی شد.",
            ]);
        }
    }

    public function deleteEquipment(int $equipmentId)
    {
        $equipment = BuildingEquipment::find($equipmentId);
        if ($equipment) {
            $title = $equipment->title;
            $equipment->delete();
            $this->dispatch('notify', [
                'type' => 'success',
                'text' => "تجهیز {$title} از شناسنامه حذف گردید.",
            ]);
        }
    }

    public function toggleAddSla()
    {
        $this->showAddSlaForm = !$this->showAddSlaForm;
        if ($this->showAddSlaForm) {
            $this->slaContractNumber = 'SLA-' . date('Y') . '-' . rand(1000, 9999);
            $this->slaStartDate = now()->format('Y-m-d');
            $this->slaEndDate = now()->addYear()->format('Y-m-d');
        }
    }

    public function saveSlaContract()
    {
        $this->validate([
            'slaTitle'          => 'required|string|max:150',
            'slaContractNumber' => 'required|string|max:50',
            'slaStartDate'      => 'required|date',
            'slaEndDate'        => 'required|date|after:slaStartDate',
        ], [
            'slaTitle.required' => 'عنوان قرارداد الزامی است.',
            'slaContractNumber.required' => 'شماره قرارداد الزامی است.',
            'slaEndDate.after' => 'تاریخ پایان قرارداد باید پس از تاریخ شروع باشد.',
        ]);

        if ($this->activeBuildingId) {
            SlaContract::create([
                'building_id'               => $this->activeBuildingId,
                'contract_number'           => $this->slaContractNumber,
                'title'                     => $this->slaTitle,
                'tier'                      => $this->slaTier,
                'start_date'                => $this->slaStartDate,
                'end_date'                  => $this->slaEndDate,
                'monthly_fee'               => $this->slaMonthlyFee,
                'emergency_response_hours'  => $this->slaEmergencyHours,
                'periodic_visits_per_month' => $this->slaVisitsPerMonth,
                'status'                    => 'active',
                'terms'                     => $this->slaTerms,
            ]);

            $this->showAddSlaForm = false;
            $this->dispatch('notify', [
                'type' => 'success',
                'text' => 'قرارداد پایش و نگهداری دوره‌ای (SLA) با موفقیت فعال شد.',
            ]);
        }
    }

    public function render()
    {
        $query = Building::query()
            ->with(['client', 'equipments', 'slaContracts', 'orders'])
            ->latest();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('manager_name', 'like', "%{$this->search}%")
                  ->orWhere('manager_phone', 'like', "%{$this->search}%")
                  ->orWhere('city', 'like', "%{$this->search}%")
                  ->orWhereHas('client', function ($cq) {
                      $cq->where('full_name', 'like', "%{$this->search}%")
                         ->orWhere('phone', 'like', "%{$this->search}%");
                  });
            });
        }

        if ($this->typeFilter !== 'all') {
            $query->where('building_type', $this->typeFilter);
        }

        if ($this->slaFilter === 'has_sla') {
            $query->whereHas('slaContracts', function ($sq) {
                $sq->where('status', 'active');
            });
        } elseif ($this->slaFilter === 'no_sla') {
            $query->whereDoesntHave('slaContracts', function ($sq) {
                $sq->where('status', 'active');
            });
        }

        $buildings = $query->paginate(9);

        // Search clients for the modal
        $matchedClients = collect();
        if ($this->showModal && !empty($this->clientSearch) && !$this->selectedClientId) {
            $matchedClients = Client::query()
                ->where('full_name', 'like', "%{$this->clientSearch}%")
                ->orWhere('phone', 'like', "%{$this->clientSearch}%")
                ->take(5)
                ->get();
        }

        $provinces = ProvinceCity::getProvinces();
        $cities = ProvinceCity::getCities($this->province);

        $activeBuilding = $this->activeBuildingId
            ? Building::with(['client', 'equipments', 'slaContracts', 'orders'])->find($this->activeBuildingId)
            : null;

        return view('fieldservice::livewire.admin.buildings-manager', [
            'buildings'      => $buildings,
            'matchedClients' => $matchedClients,
            'provinces'      => $provinces,
            'cities'         => $cities,
            'activeBuilding' => $activeBuilding,
        ]);
    }
}
