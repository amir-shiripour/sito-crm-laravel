<?php

namespace Modules\FieldService\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Clients\Entities\Client;
use Modules\FieldService\App\Models\Building;
use Modules\FieldService\App\Models\BuildingEquipment;

class BuildingsManager extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;

    public string $name = '';
    public string $building_type = 'residential';
    public int $units_count = 12;
    public int $floors_count = 5;
    public string $manager_name = '';
    public string $manager_phone = '';
    public string $city = 'بابل';
    public string $address = '';

    public function openCreateModal()
    {
        $this->reset(['name', 'manager_name', 'manager_phone', 'address']);
        $this->showModal = true;
    }

    public function saveBuilding()
    {
        $this->validate([
            'name'          => 'required|string|max:150',
            'manager_name'  => 'required|string|max:100',
            'manager_phone' => 'required|string|max:20',
            'city'          => 'required|string|max:60',
            'address'       => 'required|string',
        ]);

        // Find or create Client in CRM
        $client = Client::firstOrCreate(
            ['phone' => $this->manager_phone],
            ['full_name' => $this->manager_name]
        );

        Building::create([
            'client_id'     => $client->id,
            'name'          => $this->name,
            'building_type' => $this->building_type,
            'units_count'   => $this->units_count,
            'floors_count'  => $this->floors_count,
            'manager_name'  => $this->manager_name,
            'manager_phone' => $this->manager_phone,
            'city'          => $this->city,
            'address'       => $this->address,
        ]);

        $this->showModal = false;
        $this->dispatch('notify', [
            'type' => 'success',
            'text' => "شناسنامه مجتمع {$this->name} با موفقیت ثبت شد.",
        ]);
    }

    public function render()
    {
        $query = Building::query()->with(['client', 'equipments', 'slaContracts', 'orders'])->latest();

        if (!empty($this->search)) {
            $query->where('name', 'like', "%{$this->search}%")
                  ->orWhere('manager_name', 'like', "%{$this->search}%")
                  ->orWhere('city', 'like', "%{$this->search}%");
        }

        $buildings = $query->paginate(10);

        return view('fieldservice::livewire.admin.buildings-manager', [
            'buildings' => $buildings,
        ]);
    }
}
