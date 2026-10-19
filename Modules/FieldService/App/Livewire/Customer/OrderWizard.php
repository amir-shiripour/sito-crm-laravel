<?php

namespace Modules\FieldService\App\Livewire\Customer;

use App\Helpers\ProvinceCity;
use Livewire\Component;
use Modules\Clients\Entities\Client;
use Modules\Clients\Entities\ClientAddress;
use Modules\FieldService\App\Enums\ApplianceType;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Models\Category;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\VendorPartner;
use Modules\FieldService\App\Services\DispatchRadarService;
use Modules\FieldService\App\Services\FieldServiceWorkflowBridgeService;

class OrderWizard extends Component
{
    // Mode: 'create' (register order wizard) or 'lookup' (track existing order)
    public string $mode = 'create';
    public string $lookupPhone = '';
    public string $lookupOrderNumber = '';
    public string $lookupError = '';

    // Wizard Step: 1: Category, 2: Details, 3: Contact & Address, 4: Live Tracking
    public int $step = 1;

    // Form inputs
    public ?int $selectedCategoryId = null;
    public string $appliance_type = 'package';
    public string $appliance_brand_model = '';
    public string $problem_description = '';
    public string $priority = 'normal';

    public string $province = 'مازندران';
    public string $city = 'بابل';
    public string $district = '';
    public string $location_name = 'مازندران، بابل';
    public string $address = '';
    public string $building_details = '';
    public float $lat = 36.5444;
    public float $lng = 52.6780;

    public string $customer_name = '';
    public string $customer_phone = '';
    public ?string $referral_code = null;
    public string $referralStatusMessage = '';

    public ?int $createdOrderId = null;

    protected $listeners = [
        'map-coordinates-updated' => 'updateCoordinates',
        'city-selected'           => 'selectCity',
    ];

    protected $rules = [
        1 => ['selectedCategoryId' => 'required|exists:field_service_categories,id'],
        2 => ['problem_description' => 'required|string|min:3'],
        3 => [
            'customer_name'  => 'required|string|min:3',
            'customer_phone' => 'required|string|min:10',
            'province'       => 'required|string',
            'city'           => 'required|string',
            'address'        => 'required|string|min:3',
        ],
    ];

    public function mount()
    {
        $defaultCat = Category::where('is_active', true)->orderBy('sort_order')->first();
        if ($defaultCat) {
            $this->selectedCategoryId = $defaultCat->id;
            $this->appliance_type = $defaultCat->appliance_type?->value ?? 'package';
        }

        // Auto-check if order_id is in query string
        $orderIdParam = request()->query('order_id');
        if ($orderIdParam) {
            $existing = ServiceOrder::find($orderIdParam);
            if ($existing) {
                $this->createdOrderId = $existing->id;
                $this->step = 4;
            }
        }
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

    public function updatedReferralCode($value)
    {
        $value = trim((string) $value);
        if (!empty($value)) {
            $vendor = VendorPartner::where('referral_code', $value)->where('status', 'active')->first();
            if ($vendor) {
                $this->referralStatusMessage = "فروشگاه همکار: {$vendor->store_name} ({$vendor->city}) تایید شد.";
            } else {
                $this->referralStatusMessage = "کد معرف نامعتبر است.";
            }
        } else {
            $this->referralStatusMessage = '';
        }
    }

    public function updateCoordinates(float $lat, float $lng, ?string $cityName = null, ?string $locationText = null)
    {
        $this->lat = $lat;
        $this->lng = $lng;

        if (!empty($cityName)) {
            $this->city = $cityName;
        }

        if (!empty($locationText)) {
            $this->location_name = $locationText;
            if (empty($this->address)) {
                $this->address = $locationText;
            }
        }
    }

    public function selectCity(string $cityName)
    {
        $this->city = $cityName;
        $cityCoords = [
            'بابل'       => [36.5444, 52.6780],
            'ساری'       => [36.5659, 53.0586],
            'آمل'        => [36.4676, 52.3507],
            'قائمشهر'    => [36.4646, 52.8596],
            'بابلسر'     => [36.7024, 52.6575],
            'محمودآباد'  => [36.6319, 52.2629],
        ];

        if (isset($cityCoords[$cityName])) {
            $this->lat = $cityCoords[$cityName][0];
            $this->lng = $cityCoords[$cityName][1];
            $this->location_name = "مازندران، {$cityName}";
            $this->dispatch('center-map-on-coords', lat: $this->lat, lng: $this->lng, zoom: 14);
        }
    }

    public function addProblemTag(string $tag)
    {
        $current = trim($this->problem_description);
        if (empty($current)) {
            $this->problem_description = $tag;
        } elseif (!str_contains($current, $tag)) {
            $this->problem_description = $current . '، ' . $tag;
        }
    }

    public function setMode(string $newMode)
    {
        $this->mode = $newMode;
        $this->lookupError = '';
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

        $fullAddress = trim($this->address);
        if (!empty($this->building_details)) {
            $fullAddress .= ' (' . trim($this->building_details) . ')';
        }

        // 1. Strictly manage customer via CRM Clients module (Auto-register client)
        $client = Client::where('phone', $this->customer_phone)->first();
        if (!$client) {
            $username = $this->customer_phone;
            if (Client::where('username', $username)->exists()) {
                $username .= '_' . \Illuminate\Support\Str::random(4);
            }
            $client = Client::create([
                'username'  => $username,
                'full_name' => $this->customer_name,
                'phone'     => $this->customer_phone,
            ]);
        }

        // Update address in client addresses if not present
        if ($client->addresses()->count() === 0 && class_exists(ClientAddress::class)) {
            $client->addresses()->create([
                'title'      => 'محل سکونت',
                'province'   => $this->province,
                'city'       => $this->city,
                'address'    => $fullAddress,
                'is_default' => true,
            ]);
        }

        // 2. Resolve Vendor Partner if referral_code provided
        $vendorId = null;
        if (!empty($this->referral_code)) {
            $vendor = VendorPartner::where('referral_code', trim($this->referral_code))->where('status', 'active')->first();
            if ($vendor) {
                $vendorId = $vendor->id;
            }
        }

        // 3. Create Service Order
        $order = ServiceOrder::create([
            'order_number'          => ServiceOrder::generateOrderNumber(),
            'client_id'             => $client->id,
            'category_id'           => $this->selectedCategoryId,
            'vendor_id'             => $vendorId,
            'appliance_type'        => ApplianceType::tryFrom($this->appliance_type) ?? ApplianceType::PACKAGE,
            'appliance_brand_model' => $this->appliance_brand_model,
            'problem_description'   => $this->problem_description,
            'status'                => OrderStatus::SEARCHING,
            'priority'              => $this->priority,
            'is_emergency'          => $this->priority === 'emergency',
            'province'              => $this->province,
            'city'                  => $this->city,
            'district'              => $this->district,
            'address'               => $fullAddress,
            'lat'                   => $this->lat,
            'lng'                   => $this->lng,
            'customer_name'         => $this->customer_name,
            'customer_phone'        => $this->customer_phone,
            'search_radius_km'      => 3,
            'dispatched_at'         => now(),
        ]);

        $this->createdOrderId = $order->id;

        // 4. Trigger initial workflow event
        app(FieldServiceWorkflowBridgeService::class)->triggerOrderCreated($order);

        // 5. Activate Snapp-like dispatch radar
        app(DispatchRadarService::class)->dispatchOrder($order);

        $this->step = 4;
        $this->dispatch('order-created', orderId: $order->id);
        $this->dispatch('start-radar-animation', lat: $this->lat, lng: $this->lng, radiusKm: 3);
    }

    public function lookupOrder()
    {
        $this->lookupError = '';

        if (empty($this->lookupPhone) && empty($this->lookupOrderNumber)) {
            $this->lookupError = 'لطفاً شماره همراه یا کد پیگیری سفارش را وارد کنید.';
            return;
        }

        $query = ServiceOrder::query();

        if (!empty($this->lookupOrderNumber)) {
            $query->where('order_number', trim($this->lookupOrderNumber));
        } elseif (!empty($this->lookupPhone)) {
            $query->where('customer_phone', trim($this->lookupPhone))
                  ->whereNotIn('status', [OrderStatus::COMPLETED, OrderStatus::CANCELLED]);
        }

        $order = $query->latest()->first();

        if ($order) {
            $this->createdOrderId = $order->id;
            $this->mode = 'create';
            $this->step = 4;
        } else {
            $this->lookupError = 'سفارش فعالی با این مشخصات یافت نشد.';
        }
    }

    public function startNewOrder()
    {
        $this->reset([
            'step',
            'createdOrderId',
            'appliance_brand_model',
            'problem_description',
            'district',
            'address',
            'referral_code',
            'referralStatusMessage',
        ]);
        $this->mode = 'create';
        $this->step = 1;
    }

    public function render()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $provinces = ProvinceCity::getProvinces();
        $cities = ProvinceCity::getCities($this->province);

        return view('fieldservice::livewire.customer.order-wizard', [
            'categories' => $categories,
            'provinces'  => $provinces,
            'cities'     => $cities,
        ]);
    }
}
