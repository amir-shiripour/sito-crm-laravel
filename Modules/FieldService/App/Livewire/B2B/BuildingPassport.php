<?php

namespace Modules\FieldService\App\Livewire\B2B;

use Livewire\Component;
use Modules\FieldService\App\Models\Building;
use Modules\FieldService\App\Models\BuildingEquipment;

class BuildingPassport extends Component
{
    public ?int $buildingId = null;

    public bool $showAddEquipModal = false;
    public string $equipment_type = 'boiler_room';
    public string $title = '';
    public string $brand_model = '';
    public string $serial_number = '';
    public string $installation_year = '1401';
    public string $health_status = 'good';

    public function mount(?int $buildingId = null)
    {
        $this->buildingId = $buildingId ?: Building::first()?->id;
    }

    public function addEquipment()
    {
        $this->validate([
            'title' => 'required|string|max:120',
        ]);

        if ($this->buildingId) {
            BuildingEquipment::create([
                'building_id'       => $this->buildingId,
                'equipment_type'    => $this->equipment_type,
                'title'             => $this->title,
                'brand_model'       => $this->brand_model,
                'serial_number'     => $this->serial_number,
                'installation_year' => $this->installation_year,
                'health_status'     => $this->health_status,
                'last_serviced_at'  => now(),
            ]);

            $this->showAddEquipModal = false;
            $this->reset(['title', 'brand_model', 'serial_number']);

            $this->dispatch('notify', [
                'type' => 'success',
                'text' => 'تجهیز جدید با موفقیت به شناسنامه فنی ساختمان اضافه شد.',
            ]);
        }
    }

    public function render()
    {
        $building = Building::with(['client', 'equipments', 'slaContracts', 'orders'])
            ->find($this->buildingId);

        return view('fieldservice::livewire.b2b.building-passport', [
            'building' => $building,
        ]);
    }
}
