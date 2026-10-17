<?php

namespace Modules\FieldService\App\Livewire\Customer;

use Livewire\Component;
use Modules\Clients\Entities\Client;
use Modules\FieldService\App\Enums\ApplianceType;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Models\Category;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Services\DispatchRadarService;
use Modules\FieldService\App\Services\FieldServiceWorkflowBridgeService;

class OrderWizard extends Component
{
    public int $step = 1; // 1: Category, 2: Details, 3: Location, 4: Tracking

    // Form inputs
    public ?int $selectedCategoryId = null;
    public string $appliance_type = 'package';
    public string $appliance_brand_model = '';
    public string $problem_description = '';
    public string $priority = 'normal';

    public string $province = 'مازندران';
    public string $city = 'بابل';
    public string $district = '';
    public string $address = '';
    public float $lat = 36.5444;
    public float $lng = 52.6780;

    public string $customer_name = '';
    public string $customer_phone = '';

    public ?int $createdOrderId = null;

    protected $rules = [
        1 => ['selectedCategoryId' => 'required|exists:field_service_categories,id'],
        2 => ['problem_description' => 'required|string|min:5'],
        3 => [
            'customer_name'  => 'required|string|min:3',
            'customer_phone' => 'required|string|min:10',
            'city'           => 'required|string',
            'address'        => 'required|string|min:5',
        ],
    ];

    public function mount()
    {
        $defaultCat = Category::where('is_active', true)->orderBy('sort_order')->first();
        if ($defaultCat) {
            $this->selectedCategoryId = $defaultCat->id;
            $this->appliance_type = $defaultCat->appliance_type?->value ?? 'package';
        }
    }

    public function selectCategory(int $catId)
    {
        $cat = Category::find($catId);
        if ($cat) {
            $this->selectedCategoryId = $cat->id;
            $this->appliance_type = $cat->appliance_type?->value ?? 'package';
            $this->step = 2;
        }
    }

    public function nextStep()
    {
        if (isset($this->rules[$this->step])) {
            $this->validate($this->rules[$this->step]);
        }
        $this->step++;
    }

    public function prevStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function submitOrder()
    {
        $this->validate($this->rules[3]);

        // 1. Strictly manage customer via CRM Clients module
        $client = Client::firstOrCreate(
            ['phone' => $this->customer_phone],
            ['full_name' => $this->customer_name]
        );

        // 2. Create Service Order
        $order = ServiceOrder::create([
            'order_number'          => ServiceOrder::generateOrderNumber(),
            'client_id'             => $client->id,
            'category_id'           => $this->selectedCategoryId,
            'appliance_type'        => ApplianceType::tryFrom($this->appliance_type) ?? ApplianceType::PACKAGE,
            'appliance_brand_model' => $this->appliance_brand_model,
            'problem_description'   => $this->problem_description,
            'status'                => OrderStatus::SEARCHING,
            'priority'              => $this->priority,
            'is_emergency'          => $this->priority === 'emergency',
            'province'              => $this->province,
            'city'                  => $this->city,
            'district'              => $this->district,
            'address'               => $this->address,
            'lat'                   => $this->lat,
            'lng'                   => $this->lng,
            'customer_name'         => $this->customer_name,
            'customer_phone'        => $this->customer_phone,
            'search_radius_km'      => 3,
        ]);

        $this->createdOrderId = $order->id;

        // 3. Trigger initial workflow event
        app(FieldServiceWorkflowBridgeService::class)->triggerOrderCreated($order);

        // 4. Activate Snapp-like dispatch radar
        app(DispatchRadarService::class)->dispatchOrder($order);

        $this->step = 4;
        $this->dispatch('order-created', orderId: $order->id);
    }

    public function render()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('fieldservice::livewire.customer.order-wizard', [
            'categories' => $categories,
        ]);
    }
}
