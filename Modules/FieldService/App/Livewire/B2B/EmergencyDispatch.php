<?php

namespace Modules\FieldService\App\Livewire\B2B;

use Livewire\Component;
use Modules\Clients\Entities\Client;
use Modules\FieldService\App\Enums\ApplianceType;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Models\Building;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Services\DispatchRadarService;
use Modules\FieldService\App\Services\FieldServiceWorkflowBridgeService;

class EmergencyDispatch extends Component
{
    public ?int $buildingId = null;
    public string $appliance_type = 'boiler';
    public string $emergency_title = '';
    public string $problem_description = '';

    public function mount(?int $buildingId = null)
    {
        $this->buildingId = $buildingId ?: Building::first()?->id;
    }

    public function dispatchEmergency()
    {
        $this->validate([
            'problem_description' => 'required|string|min:5',
        ]);

        $building = Building::find($this->buildingId);
        if (!$building) return;

        // Ensure Client exists for the building
        $client = $building->client ?: Client::firstOrCreate(
            ['phone' => $building->manager_phone ?: '09110000000'],
            ['full_name' => $building->manager_name ?: 'مدیر مجتمع']
        );

        $order = ServiceOrder::create([
            'order_number'          => ServiceOrder::generateOrderNumber(),
            'client_id'             => $client->id,
            'building_id'           => $building->id,
            'appliance_type'        => ApplianceType::tryFrom($this->appliance_type) ?? ApplianceType::BOILER,
            'appliance_brand_model' => $this->emergency_title ?: 'تاسیسات مجتمع ' . $building->name,
            'problem_description'   => "🚨 فوریت اضطراری تاسیسات: " . $this->problem_description,
            'status'                => OrderStatus::SEARCHING,
            'priority'              => 'emergency',
            'is_emergency'          => true,
            'province'              => $building->province,
            'city'                  => $building->city,
            'address'               => $building->address,
            'lat'                   => $building->lat ?: 36.5444,
            'lng'                   => $building->lng ?: 52.6780,
            'customer_name'         => $building->manager_name ?: $client->full_name,
            'customer_phone'        => $building->manager_phone ?: $client->phone,
            'search_radius_km'      => 7, // Wider radius for emergency
        ]);

        app(FieldServiceWorkflowBridgeService::class)->triggerOrderCreated($order);
        app(DispatchRadarService::class)->dispatchOrder($order);

        $this->reset(['emergency_title', 'problem_description']);

        $this->dispatch('notify', [
            'type' => 'error',
            'text' => "هشدار اضطراری برای مجتمع {$building->name} در شبکه رادار استان مازندران فعال شد و سریع‌ترین تیم اعزام گردید.",
        ]);
    }

    public function render()
    {
        $buildings = Building::all();
        return view('fieldservice::livewire.b2b.emergency-dispatch', [
            'buildings' => $buildings,
        ]);
    }
}
