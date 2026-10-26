<?php

namespace Modules\FieldService\App\Livewire\Customer;

use App\Helpers\ProvinceCity;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\Clients\Entities\Client;
use Modules\Clients\Entities\ClientAddress;
use Modules\FieldService\App\Enums\ApplianceType;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Models\Category;
use Modules\FieldService\App\Models\FieldServiceSetting;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\VendorPartner;
use Modules\FieldService\App\Services\DispatchRadarService;
use Modules\FieldService\App\Services\FieldServiceWorkflowBridgeService;
use Modules\FieldService\App\Services\ReverseGeocodingService;

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
    public bool $isAuthenticated = false;
    public ?int $clientId = null;
    public ?string $referral_code = null;
    public string $referralStatusMessage = '';

    // Snapp-style order delegation: 'self' (default) or 'other'
    public string $order_for = 'self';
    public string $recipient_name = '';
    public string $recipient_phone = '';
    public array $savedAddresses = [];

    // Pricing Calculation (in Tomans)
    public float $baseFee = 350000;
    public float $dispatchFee = 0;
    public float $totalEstimatedFee = 350000;

    // Sub-tab navigation in Step 2: 'main', 'options', 'discount', 'all_services'
    public string $activeSubTab = 'main';
    public string $categorySearch = '';

    public ?int $createdOrderId = null;

    protected $listeners = [
        'map-coordinates-updated'     => 'updateCoordinates',
        'city-selected'               => 'selectCity',
        'client-authenticated'        => 'syncAuthenticatedClient',
        'client-logged-out'           => 'syncLoggedOutClient',
        'set-order-for'               => 'setOrderFor',
        'select-saved-address'        => 'selectSavedAddress',
        'confirm-origin'              => 'confirmLocation',
        'confirm-location'            => 'confirmLocation',
        'return-to-map'               => 'returnToMap',
        'start-new-order'             => 'startNewOrder',
        'order-cancelled-by-customer' => '$refresh',
    ];

    protected function rules(): array
    {
        $step3Rules = [
            'problem_description' => 'required|string|min:3',
            'address'             => 'required|string|min:3',
        ];

        if ($this->order_for === 'other') {
            $step3Rules['recipient_name'] = 'required|string|min:3';
            $step3Rules['recipient_phone'] = 'required|string|min:10';
        }

        return [
            1 => ['address' => 'required|string|min:2'],
            2 => [
                'selectedCategoryId' => 'required|exists:field_service_categories,id',
                'priority'           => 'required|in:normal,emergency',
            ],
            3 => $step3Rules,
        ];
    }

    public function mount()
    {
        $this->syncAuthUser();

        $defaultCat = Category::where('is_active', true)->orderBy('sort_order')->first();
        if ($defaultCat) {
            $this->selectedCategoryId = $defaultCat->id;
            $this->appliance_type = $defaultCat->appliance_type?->value ?? 'package';
        }

        $this->calculatePricing();

        if (empty($this->address)) {
            $geo = app(ReverseGeocodingService::class)->reverse($this->lat, $this->lng);
            $this->location_name = $geo['formatted_address'];
            $this->address = $geo['formatted_address'];
        }

        // Auto-check for active unresolved order (by client ID, session, or query param)
        $clientId = Auth::guard('client')->id();
        $sessionOrderId = session('fieldservice_active_order_id');
        $orderIdParam = request()->query('order_id');

        $activeOrder = ServiceOrder::resolveActiveForClient($clientId, $sessionOrderId, $orderIdParam);
        if ($activeOrder) {
            $this->createdOrderId = $activeOrder->id;
            $this->step = 4;
            $this->lat = (float) $activeOrder->lat;
            $this->lng = (float) $activeOrder->lng;
            $this->address = $activeOrder->address ?: '';
            $this->location_name = $activeOrder->address ?: '';
            session(['fieldservice_active_order_id' => $activeOrder->id]);
        }
    }

    public function calculatePricing(): void
    {
        $cat = $this->selectedCategoryId ? Category::find($this->selectedCategoryId) : null;
        if ($cat && $cat->base_labor_fee) {
            $this->baseFee = round($cat->base_labor_fee / 10);
        } else {
            $this->baseFee = 350000;
        }

        // Emergency fee: 100,000 Tomans for immediate priority dispatch
        $this->dispatchFee = ($this->priority === 'emergency') ? 100000 : 0;
        $this->totalEstimatedFee = $this->baseFee + $this->dispatchFee;
    }

    public function setPriority(string $priority): void
    {
        $this->priority = in_array($priority, ['normal', 'emergency'], true) ? $priority : 'normal';
        $this->calculatePricing();
    }

    public function syncAuthUser()
    {
        $client = Auth::guard('client')->user();
        if ($client) {
            $this->isAuthenticated = true;
            $this->clientId = $client->id;
            $this->customer_name = $client->full_name ?: 'کاربر گرامی';
            $this->customer_phone = $client->phone ?: '';

            // Load saved addresses
            $this->savedAddresses = $client->addresses()->latest()->take(6)->get()->toArray();

            // Load default address if available
            $defaultAddr = $client->addresses()->where('is_default', true)->first() ?: $client->addresses()->latest()->first();
            if ($defaultAddr && empty($this->address)) {
                $this->province = $defaultAddr->province ?: $this->province;
                $this->city = $defaultAddr->city ?: $this->city;
                $this->address = $defaultAddr->address ?: $this->address;
                if ($defaultAddr->lat && $defaultAddr->lng) {
                    $this->lat = (float) $defaultAddr->lat;
                    $this->lng = (float) $defaultAddr->lng;
                    $this->location_name = "مازندران، {$this->city}";
                }
            }
        } else {
            $this->isAuthenticated = false;
            $this->clientId = null;
            $this->savedAddresses = [];
        }
    }

    public function setOrderFor(string $target)
    {
        $this->order_for = in_array($target, ['self', 'other'], true) ? $target : 'self';
    }

    public function selectSavedAddress(int $addressId)
    {
        if (!$this->isAuthenticated || !$this->clientId) {
            $this->dispatch('open-customer-auth');
            return;
        }

        $addr = ClientAddress::where('client_id', $this->clientId)->find($addressId);
        if ($addr) {
            $this->province = $addr->province ?: $this->province;
            $this->city = $addr->city ?: $this->city;
            $this->address = $addr->address ?: $this->address;
            if ($addr->lat && $addr->lng) {
                $this->lat = (float) $addr->lat;
                $this->lng = (float) $addr->lng;
                $this->location_name = "مازندران، {$this->city} - {$addr->title}";
                $this->dispatch('center-map-on-coords', lat: $this->lat, lng: $this->lng, zoom: 15);
            }
        }
    }

    public function confirmLocationWithAddress(float $lat, float $lng, ?string $locationText = null, ?string $cityName = null)
    {
        $this->lat = $lat;
        $this->lng = $lng;

        if (!empty($cityName)) {
            $this->city = $cityName;
        }

        if (!empty($locationText)) {
            $this->location_name = $locationText;
            $this->address = $locationText;
        }

        $this->confirmLocation();
    }

    public function confirmLocation()
    {
        if (!$this->isAuthenticated) {
            $this->dispatch('open-customer-auth');
            return;
        }

        $this->step = 2;
        $this->activeSubTab = 'main';
        $this->calculatePricing();
        $this->dispatch('location-confirmed', lat: $this->lat, lng: $this->lng, locationText: $this->location_name);
        $this->dispatch('origin-confirmed', lat: $this->lat, lng: $this->lng, locationText: $this->location_name);
        $this->dispatch('expand-sheet');
        $this->dispatch('order-step-changed', step: $this->step);
    }

    public function confirmOrigin()
    {
        $this->confirmLocation();
    }

    public function returnToMap()
    {
        $this->step = 1;
        $this->activeSubTab = 'main';
        $this->dispatch('location-cleared');
        $this->dispatch('origin-cleared');
        $this->dispatch('collapse-sheet');
        $this->dispatch('order-step-changed', step: $this->step);
    }

    public function syncAuthenticatedClient($data = [])
    {
        $this->syncAuthUser();

        if ($this->clientId && $this->step < 4) {
            $activeOrder = ServiceOrder::resolveActiveForClient($this->clientId);
            if ($activeOrder) {
                $this->createdOrderId = $activeOrder->id;
                $this->step = 4;
                $this->lat = (float) $activeOrder->lat;
                $this->lng = (float) $activeOrder->lng;
                $this->address = $activeOrder->address ?: '';
                $this->location_name = $activeOrder->address ?: '';
                session(['fieldservice_active_order_id' => $activeOrder->id]);

                $this->dispatch('active-order-resumed', [
                    'orderId' => $activeOrder->id,
                    'lat' => (float) $activeOrder->lat,
                    'lng' => (float) $activeOrder->lng,
                    'status' => $activeOrder->status->value,
                    'technician' => $activeOrder->technician ? [
                        'lat' => (float) $activeOrder->technician->lat,
                        'lng' => (float) $activeOrder->technician->lng,
                        'name' => $activeOrder->technician->full_name,
                        'mobile' => $activeOrder->technician->mobile,
                    ] : null,
                ]);
            }
        }
    }

    public function syncLoggedOutClient()
    {
        $this->isAuthenticated = false;
        $this->clientId = null;
        $this->customer_name = '';
        $this->customer_phone = '';
        session()->forget('fieldservice_active_order_id');
    }

    public function updatedProvince($value)
    {
        $provinceCities = ProvinceCity::getCities($value);
        $coveredCities = FieldServiceSetting::getCoveredCities();
        $cities = array_values(array_intersect($provinceCities, $coveredCities));
        if (empty($cities)) {
            $cities = $provinceCities;
        }

        if (in_array('بابل', $cities, true)) {
            $this->city = 'بابل';
        } else {
            $this->city = $cities[0] ?? '';
        }

        if (!empty($this->city)) {
            $this->selectCity($this->city);
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
            $this->address = $locationText;
        } else {
            $geo = app(ReverseGeocodingService::class)->reverse($lat, $lng);
            $this->location_name = $geo['formatted_address'];
            $this->address = $geo['formatted_address'];
        }
    }

    public function selectCity(string $cityName)
    {
        $this->city = $cityName;
        $cityCoords = FieldServiceSetting::getCoveredCitiesWithCoords();

        if (isset($cityCoords[$cityName])) {
            $this->lat = (float) $cityCoords[$cityName]['lat'];
            $this->lng = (float) $cityCoords[$cityName]['lng'];
            $geo = app(ReverseGeocodingService::class)->reverse($this->lat, $this->lng);
            $this->location_name = $geo['formatted_address'];
            $this->address = $geo['formatted_address'];
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
        if (!$this->isAuthenticated) {
            $this->dispatch('open-customer-auth');
            return;
        }

        $this->mode = $newMode;
        $this->lookupError = '';
    }

    public function selectCategory(int $catId)
    {
        if (!$this->isAuthenticated) {
            $this->dispatch('open-customer-auth');
            return;
        }

        $cat = Category::find($catId);
        if ($cat) {
            $this->selectedCategoryId = $cat->id;
            $this->appliance_type = $cat->appliance_type?->value ?? 'package';
            $this->calculatePricing();
        }
    }

    public function selectCategoryAndReturn(int $catId)
    {
        $this->selectCategory($catId);
        $this->activeSubTab = 'main';
    }

    public function goToStep3()
    {
        if (!$this->isAuthenticated) {
            $this->dispatch('open-customer-auth');
            return;
        }

        $this->validate([
            'selectedCategoryId' => 'required|exists:field_service_categories,id',
            'priority'           => 'required|in:normal,emergency',
        ]);

        $this->step = 3;
        $this->dispatch('order-step-changed', step: $this->step);
    }

    public function goToStep2()
    {
        $this->step = 2;
        $this->dispatch('order-step-changed', step: $this->step);
    }

    public function nextStep()
    {
        if (!$this->isAuthenticated) {
            $this->dispatch('open-customer-auth');
            return;
        }

        $rules = $this->rules();
        if (isset($rules[$this->step])) {
            $this->validate($rules[$this->step]);
        }
        $this->step++;
        $this->dispatch('order-step-changed', step: $this->step);
    }

    public function prevStep()
    {
        if ($this->step > 1) {
            $this->step--;
            $this->dispatch('order-step-changed', step: $this->step);
        }
    }

    public function setSubTab(string $tab): void
    {
        $this->activeSubTab = in_array($tab, ['main', 'options', 'discount', 'all_services'], true) ? $tab : 'main';
        if ($tab !== 'all_services') {
            $this->categorySearch = '';
        }
    }

    public function submitOrder()
    {
        if (!$this->isAuthenticated) {
            $this->dispatch('open-customer-auth');
            return;
        }

        // Validate recipient if order is for other
        if ($this->order_for === 'other') {
            if (empty($this->recipient_phone) || empty($this->recipient_name)) {
                $this->activeSubTab = 'options';
                $this->validate([
                    'recipient_name'  => 'required|string|min:3',
                    'recipient_phone' => 'required|string|min:10',
                ]);
                return;
            }
        }

        if (empty($this->problem_description)) {
            $this->problem_description = 'درخواست بررسی و کارشناسی فنی در محل';
        }

        if (empty($this->address)) {
            $this->address = $this->location_name ?: "مازندران، {$this->city}";
        }

        $fullAddress = trim($this->address);
        if (!empty($this->building_details)) {
            $fullAddress .= ' (' . trim($this->building_details) . ')';
        }

        // 1. Strictly manage customer via CRM Clients module (Auto-register or fetch logged in client)
        if ($this->isAuthenticated && $this->clientId) {
            $client = Client::find($this->clientId);
        } else {
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
        }

        // Guard: Prevent duplicate order if customer already has an active unresolved order
        if ($client) {
            $existingActive = ServiceOrder::resolveActiveForClient($client->id);
            if ($existingActive) {
                $this->createdOrderId = $existingActive->id;
                $this->step = 4;
                session(['fieldservice_active_order_id' => $existingActive->id]);
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'text' => 'شما دارای یک درخواست در حال پیگیری باز هستید و به صفحه رهگیری متصل شدید.',
                ]);
                $this->dispatch('active-order-resumed', [
                    'orderId'    => $existingActive->id,
                    'lat'        => (float) $existingActive->lat,
                    'lng'        => (float) $existingActive->lng,
                    'status'     => $existingActive->status->value,
                    'technician' => $existingActive->technician ? [
                        'lat'    => (float) $existingActive->technician->lat,
                        'lng'    => (float) $existingActive->technician->lng,
                        'name'   => $existingActive->technician->full_name,
                        'mobile' => $existingActive->technician->mobile,
                    ] : null,
                ]);
                return;
            }
        }

        // Update address in client addresses if not present
        if ($client && class_exists(ClientAddress::class)) {
            $existingAddress = $client->addresses()->where('address', $fullAddress)->first();
            if (!$existingAddress) {
                $client->addresses()->create([
                    'title'      => 'محل اعزام تکنسین',
                    'province'   => $this->province,
                    'city'       => $this->city,
                    'address'    => $fullAddress,
                    'lat'        => $this->lat,
                    'lng'        => $this->lng,
                    'is_default' => $client->addresses()->count() === 0,
                ]);
            }
        }

        // 2. Resolve Vendor Partner if referral_code provided
        $vendorId = null;
        if (!empty($this->referral_code)) {
            $vendor = VendorPartner::where('referral_code', trim($this->referral_code))->where('status', 'active')->first();
            if ($vendor) {
                $vendorId = $vendor->id;
            }
        }

        // 3. Resolve recipient name & phone based on order_for
        $customerName = ($this->order_for === 'other' && !empty($this->recipient_name)) ? trim($this->recipient_name) : ($client->full_name ?: $this->customer_name);
        $customerPhone = ($this->order_for === 'other' && !empty($this->recipient_phone)) ? trim($this->recipient_phone) : ($client->phone ?: $this->customer_phone);

        $orderNotes = null;
        if ($this->order_for === 'other' && !empty($this->recipient_name)) {
            $orderNotes = "سفارش توسط کاربر {$client->full_name} ({$client->phone}) برای تحویل‌گیرنده در محل ثبت شده است: {$this->recipient_name} ({$this->recipient_phone})";
        }

        // 4. Create Service Order
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
            'customer_name'         => $customerName,
            'customer_phone'        => $customerPhone,
            'notes'                 => $orderNotes,
            'search_radius_km'      => 3,
            'dispatched_at'         => now(),
        ]);

        $this->createdOrderId = $order->id;
        session(['fieldservice_active_order_id' => $order->id]);

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
        if (!$this->isAuthenticated) {
            $this->dispatch('open-customer-auth');
            return;
        }

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
            if ($order->isActive()) {
                session(['fieldservice_active_order_id' => $order->id]);
            }
            $this->dispatch('active-order-resumed', [
                'orderId'    => $order->id,
                'lat'        => (float) $order->lat,
                'lng'        => (float) $order->lng,
                'status'     => $order->status->value,
                'technician' => $order->technician ? [
                    'lat'    => (float) $order->technician->lat,
                    'lng'    => (float) $order->technician->lng,
                    'name'   => $order->technician->full_name,
                    'mobile' => $order->technician->mobile,
                ] : null,
            ]);
        } else {
            $this->lookupError = 'سفارش فعالی با این مشخصات یافت نشد.';
        }
    }

    public function startNewOrder()
    {
        if ($this->createdOrderId) {
            $order = ServiceOrder::find($this->createdOrderId);
            if ($order && $order->isActive()) {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'text' => 'امکان ثبت سفارش جدید وجود ندارد. لطفاً ابتدا سفارش فعال فعلی را به اتمام رسانده یا لغو نمایید.',
                ]);
                return;
            }
        }

        session()->forget('fieldservice_active_order_id');
        $this->reset([
            'step',
            'createdOrderId',
            'appliance_brand_model',
            'problem_description',
            'district',
            'address',
            'referral_code',
            'referralStatusMessage',
            'activeSubTab',
        ]);
        $this->priority = 'normal';
        $this->calculatePricing();
        $this->mode = 'create';
        $this->step = 1;
        $this->dispatch('location-cleared');
        $this->dispatch('origin-cleared');
        $this->dispatch('collapse-sheet');
        $this->dispatch('order-step-changed', step: 1);
    }

    public function render()
    {
        $allCategories = Category::where('is_active', true)->orderBy('sort_order')->get();
        
        // Auto-select first category if none selected
        if (!$this->selectedCategoryId && $allCategories->isNotEmpty()) {
            $this->selectedCategoryId = $allCategories->first()->id;
            $this->appliance_type = $allCategories->first()->appliance_type?->value ?? 'package';
            $this->calculatePricing();
        }

        // Determine categories for the 2-column main view:
        // Show up to 3 top categories; if the user's selected category is beyond top 3, show it in the 3rd slot!
        $mainCategories = collect();
        if ($allCategories->count() <= 4) {
            $mainCategories = $allCategories;
        } else {
            $top3 = $allCategories->take(3);
            $isSelectedInTop3 = $top3->contains('id', $this->selectedCategoryId);

            if (!$isSelectedInTop3 && $this->selectedCategoryId) {
                $selectedCat = $allCategories->firstWhere('id', $this->selectedCategoryId);
                if ($selectedCat) {
                    $mainCategories = $allCategories->take(2)->push($selectedCat);
                } else {
                    $mainCategories = $top3;
                }
            } else {
                $mainCategories = $top3;
            }
        }

        // Filtered categories for the "all_services" full catalog sub-tab
        $filteredCategories = $allCategories;
        $search = trim($this->categorySearch);
        if (!empty($search)) {
            $filteredCategories = $allCategories->filter(function ($c) use ($search) {
                return str_contains($c->name, $search) || str_contains($c->description ?? '', $search);
            });
        }

        $coveredProvinces = FieldServiceSetting::getCoveredProvinces();
        $provinces = !empty($coveredProvinces) ? $coveredProvinces : ProvinceCity::getProvinces();

        $provinceCities = ProvinceCity::getCities($this->province);
        $coveredCities = FieldServiceSetting::getCoveredCities();
        $intersectCities = array_values(array_intersect($provinceCities, $coveredCities));
        $cities = !empty($intersectCities) ? $intersectCities : $provinceCities;

        return view('fieldservice::livewire.customer.order-wizard', [
            'categories'         => $allCategories,
            'mainCategories'     => $mainCategories,
            'filteredCategories' => $filteredCategories,
            'provinces'          => $provinces,
            'cities'             => $cities,
        ]);
    }
}
