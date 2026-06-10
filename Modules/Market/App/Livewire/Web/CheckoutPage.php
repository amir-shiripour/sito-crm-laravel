<?php

namespace Modules\Market\App\Livewire\Web;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Modules\Clients\Entities\Client;
use Modules\Market\App\Models\CheckoutForm;
use Modules\Market\App\Models\Order;
use Modules\Market\App\Models\OrderItem;
use Modules\Market\App\Models\OrderMeta;
use Modules\Market\App\Services\ClientSyncService;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\VendorProduct;
use Modules\Market\App\Services\StockService;
use App\Helpers\ProvinceCity; // Helper for province/city data

class CheckoutPage extends Component
{
    public ?CheckoutForm $form = null;
    public array $schema = [];
    public array $formData = [];

    // Shipping Fields
    public $shippingMethods = [];
    public $selectedShippingMethodId = null;
    public $shippingCost = 0;
    public $availableSlots = [];
    public $selectedSlotId = null;
    public $selectedDeliveryDate = null;

    public $totals = [
        'subtotal' => 0,
        'discount' => 0,
        'grand_total' => 0,
    ];

    public $provinces = [];
    public $cities = [];
    public $selectedProvince = null;

    public string $payment_method = '';
    public array $paymentMethods = [];

    // Saved Addresses integration
    public $addresses = [];
    public $selectedAddressId = null;
    public $showNewAddressModal = false;
    public bool $showManualAddress = false;

    // Quick address creation properties
    public $newTitle = '';
    public $newProvince = '';
    public $newCity = '';
    public $newAddress = '';
    public $newPostalCode = '';
    public $newLat = 35.6892;
    public $newLng = 51.3890;

    // Search query properties
    public string $searchQuery = '';
    public array $searchResults = [];

    // Map config
    public $mapProvider = 'neshan';
    public $mapApiKey = '';

    public function updatedSearchQuery($query)
    {
        if (strlen($query) < 3) {
            $this->searchResults = [];
            return;
        }

        if (interface_exists(\Modules\Market\App\Services\Map\MapServiceInterface::class) && app()->bound(\Modules\Market\App\Services\Map\MapServiceInterface::class)) {
            $mapService = app(\Modules\Market\App\Services\Map\MapServiceInterface::class);
            $this->searchResults = $mapService->search($query, $this->newLat, $this->newLng);
        } else {
            $this->searchResults = [];
        }
    }

    public function selectSearchResult($lat, $lng, $title = '')
    {
        $this->newLat = (float)$lat;
        $this->newLng = (float)$lng;
        $this->searchResults = [];
        $this->searchQuery = $title;

        $this->fetchNewAddressFromCoordinates($lat, $lng);

        $this->dispatch('mapMoveTo', lat: $lat, lng: $lng);
    }

    public function mount(ClientSyncService $clientSyncService)
    {
        $cart = $this->getFreshCartItems();
        if (empty($cart)) {
            return redirect()->route('market.cart.index');
        }

        $allowGuestCheckout = MarketSetting::getValue('checkout.allow_guest_checkout', false);
        if (!$allowGuestCheckout && !Auth::guard('client')->check()) {
            session(['url.intended' => route('market.checkout.index')]);
            return redirect(url('/clients/login'));
        }

        $this->provinces = ProvinceCity::getProvinces();
        $this->loadForm($cart);

        if ($this->form) {
            $this->schema = $this->form->getSchema();
            $this->initializeData($clientSyncService);
        }

        $client = Auth::guard('client')->user();
        if ($client) {
            $this->addresses = $client->addresses()->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->get();
            if ($this->form) {
                $defaultAddress = $client->addresses()->where('is_default', true)->first();
                if ($defaultAddress) {
                    $this->selectAddress($defaultAddress->id);
                }
            }
        }

        if (class_exists(\Modules\Market\Entities\MarketSetting::class)) {
            $this->mapProvider = \Modules\Market\Entities\MarketSetting::getValue('map.provider', 'neshan');
            $this->mapApiKey = \Modules\Market\Entities\MarketSetting::getValue('map.api_key', '');
        }

        $this->paymentMethods = $this->getPaymentMethods();
        if (!empty($this->paymentMethods)) {
            $this->payment_method = array_key_first($this->paymentMethods);
        }

        $this->calculateTotals($cart);
        $this->loadShippingMethods();
    }

    public function updatedSelectedProvince($provinceName)
    {
        $this->cities = ProvinceCity::getCities($provinceName);
        $this->formData['city'] = null;
        $this->loadShippingMethods();
    }


    protected function findFieldIdByType($type)
    {
        foreach ($this->schema['fields'] ?? [] as $field) {
            if (($field['type'] ?? '') === $type) {
                return $field['id'];
            }
        }
        return null;
    }

    /**
     * واکشی مستقیم و تمیز دیتای سشن با اندیس‌های عددی استاندارد برای جلوگیری از تداخل لایووایر
     */
    private function getFreshCartItems(): array
    {
        $sessionCart = Session::get('market_cart', []);
        $refreshedCart = [];
        $attributeDictionary = \Modules\Market\Entities\MarketAttribute::with('values')->get();

        foreach ($sessionCart as $item) {
            if (empty($item['variant_id']) || empty($item['vendor_product_id'])) {
                continue;
            }

            $variant = ProductVariant::with('masterProduct.category')->find($item['variant_id']);
            $vp = VendorProduct::with('vendor')->find($item['vendor_product_id']);

            if ($variant && $vp) {
                $finalPrice = $vp->discount_price > 0 ? $vp->discount_price : $vp->price;

                $itemAttributes = $item['attributes'] ?? $variant->variant_attributes;
                $fullAttributes = [];
                if (is_array($itemAttributes)) {
                    foreach ($itemAttributes as $attrKey => $attrValue) {
                        $dictAttr = $attributeDictionary->firstWhere('name', $attrKey);
                        $dictVal = $dictAttr ? $dictAttr->values->firstWhere('value', $attrValue) : null;
                        
                        // مخفی کردن ویژگی استاندارد
                        if ($attrKey === 'name' && $attrValue === 'استاندارد') {
                            continue;
                        }

                        $fullAttributes[] = [
                            'key' => $attrKey,
                            'value' => $attrValue,
                            'type' => $dictAttr->type ?? 'select',
                            'meta_value' => $dictVal->meta_value ?? null,
                        ];
                    }
                }

                $refreshedCart[] = [
                    'variant_id' => (int) $variant->id,
                    'vendor_product_id' => (int) $vp->id,
                    'vendor_id' => (int) $vp->vendor_id,
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $finalPrice,
                    'base_price' => (float) $vp->price,
                    'name' => $item['title'] ?? $variant->masterProduct->title,
                    'image' => $variant->masterProduct->main_image_url,
                    'product_id' => (int) $variant->master_product_id,
                    'category_id' => (int) $variant->masterProduct->category_id,
                    'full_attributes' => $fullAttributes,
                    'slug' => $variant->masterProduct->slug,
                    'vendor_name' => $vp->vendor->store_name ?? 'نامشخص',
                ];
            }
        }

        // استفاده از array_values جهت تضمین عددی بودن مطلق ایندکس‌ها (0, 1, 2...)
        return array_values($refreshedCart);
    }

    private function calculateTotals(array $cart): void
    {
        $subtotal = 0;
        $discount = 0;

        foreach ($cart as $item) {
            $subtotal += $item['base_price'] * $item['quantity'];
            $discount += ($item['base_price'] - $item['price']) * $item['quantity'];
        }

        $this->totals['subtotal'] = $subtotal;
        $this->totals['discount'] = $discount;
        $this->totals['grand_total'] = $subtotal - $discount;
    }

    private function loadForm(array $cart): void
    {
        $cartCollection = collect($cart);
        $productIds = $cartCollection->pluck('product_id')->all();
        $categoryIds = $cartCollection->pluck('category_id')->filter()->unique()->all();

        if (MarketSetting::getValue('checkout.allow_product_override', true)) {
            $this->form = CheckoutForm::whereIn('product_id', $productIds)->where('is_active', true)->first();
            if ($this->form) return;
        }

        if (MarketSetting::getValue('checkout.allow_category_override', true)) {
            $this->form = CheckoutForm::whereIn('category_id', $categoryIds)->where('is_active', true)->first();
            if ($this->form) return;
        }

        $defaultKey = MarketSetting::getValue('checkout.default_form_key');
        $this->form = $defaultKey
            ? CheckoutForm::where('key', $defaultKey)->where('is_active', true)->first()
            : CheckoutForm::where('is_active', true)->whereNull('product_id')->whereNull('category_id')->first();
    }

    private function initializeData(ClientSyncService $clientSyncService): void
    {
        $client = Auth::guard('client')->user();

        $defaults = collect($this->schema['fields'])
            ->mapWithKeys(fn($field) => [$field['id'] => ''])
            ->all();

        $hydratedData = [];
        if ($client && $this->form) {
            $hydratedData = $clientSyncService->hydrate($client, $this->form->key);
        }

        $this->formData = array_merge($defaults, $hydratedData);
    }

    public function submit(ClientSyncService $clientSyncService, StockService $stockService)
    {
        $allowGuestCheckout = MarketSetting::getValue('checkout.allow_guest_checkout', false);
        if (!$allowGuestCheckout && !Auth::guard('client')->check()) {
            return redirect(url('/clients/login'));
        }

        $freshCart = $this->getFreshCartItems();
        if (empty($freshCart)) {
            $this->dispatch('notify', type: 'error', text: 'سبد خرید شما خالی است.');
            return;
        }

        // Geolocation ordering validation
        if (MarketSetting::getValue('orders.enable_geolocation_ordering', false)) {
            $client = Auth::guard('client')->user();
            if ($client) {
                if ($client->addresses()->count() === 0) {
                    $this->dispatch('notify', type: 'error', text: 'شما باید حداقل یک آدرس ثبت شده داشته باشید.');
                    return;
                }
                
                $selectedAddr = $client->addresses()->find($this->selectedAddressId) 
                    ?? $client->addresses()->where('is_default', true)->first() 
                    ?? $client->addresses()->first();
                if ($selectedAddr) {
                    $city = $selectedAddr->city;
                    foreach ($freshCart as $itemArray) {
                        $vendorProduct = VendorProduct::find($itemArray['vendor_product_id']);
                        if ($vendorProduct) {
                            $vendorCity = $vendorProduct->vendor->addresses()->where('is_default', true)->first()?->city 
                                ?? $vendorProduct->vendor->addresses()->first()?->city;
                            if ($vendorCity !== $city) {
                                $this->dispatch('notify', type: 'error', text: 'محصول ' . $itemArray['name'] . ' در موقعیت مکانی انتخابی شما موجود نیست.');
                                return;
                            }
                        }
                    }
                }
            } else {
                $loc = session('client_location');
                $city = $loc['city'] ?? null;
                if (!$city) {
                    $this->dispatch('notify', type: 'error', text: 'لطفاً موقعیت مکانی خود را انتخاب کنید.');
                    return;
                }
                foreach ($freshCart as $itemArray) {
                    $vendorProduct = VendorProduct::find($itemArray['vendor_product_id']);
                    if ($vendorProduct) {
                        $vendorCity = $vendorProduct->vendor->addresses()->where('is_default', true)->first()?->city 
                            ?? $vendorProduct->vendor->addresses()->first()?->city;
                        if ($vendorCity !== $city) {
                            $this->dispatch('notify', type: 'error', text: 'محصول ' . $itemArray['name'] . ' در موقعیت مکانی انتخابی شما موجود نیست.');
                            return;
                        }
                    }
                }
            }
        }

        $validationData = [
            'formData' => $this->formData,
            'payment_method' => $this->payment_method,
        ];
        $rules = $this->getDynamicRules();

        if (!empty($this->shippingMethods)) {
            $validationData['selectedShippingMethodId'] = $this->selectedShippingMethodId;
            $rules['selectedShippingMethodId'] = 'required|exists:market_shipping_methods,id';
        }

        $shippingMethod = $this->selectedShippingMethodId ? \Modules\Market\Entities\ShippingMethod::find($this->selectedShippingMethodId) : null;
        if ($shippingMethod && $shippingMethod->slots()->exists()) {
            $validationData['selectedSlotId'] = $this->selectedSlotId;
            $validationData['selectedDeliveryDate'] = $this->selectedDeliveryDate;
            $rules['selectedSlotId'] = 'required|exists:market_shipping_slots,id';
            $rules['selectedDeliveryDate'] = 'required|date';
        }

        $validator = Validator::make(
            $validationData,
            $rules,
            [
                'required' => 'فیلد «:attribute» الزامی است.',
                'in' => 'مقدار انتخاب شده برای «:attribute» نامعتبر است.',
                'selectedShippingMethodId.required' => 'انتخاب روش ارسال الزامی است.',
                'selectedSlotId.required' => 'انتخاب بازه زمانی تحویل الزامی است.',
                'selectedDeliveryDate.required' => 'انتخاب تاریخ تحویل الزامی است.',
            ],
            array_merge(
                $this->getDynamicValidationAttributes(),
                [
                    'selectedShippingMethodId' => 'روش ارسال',
                    'selectedSlotId' => 'بازه زمانی تحویل',
                    'selectedDeliveryDate' => 'تاریخ تحویل',
                ]
            )
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->dispatch('notify', type: 'error', text: $error);
            }
            $validator->validate();
        }

        $client = Auth::guard('client')->user();
        if (!$client) {
            $this->dispatch('notify', type: 'error', text: 'برای ثبت سفارش باید ابتدا وارد شوید.');
            return;
        }
        $this->calculateTotals($freshCart);

        $order = DB::transaction(function () use ($client, $freshCart, $clientSyncService, $stockService) {

            $provinceCityFieldId = $this->findFieldIdByType('select-province-city');
            $province = null;
            $city = null;

            if ($provinceCityFieldId && isset($this->formData[$provinceCityFieldId])) {
                $val = $this->formData[$provinceCityFieldId];
                $decoded = is_string($val) ? json_decode($val, true) : $val;
                if (is_array($decoded)) {
                    $province = $decoded['province'] ?? null;
                    $city = $decoded['city'] ?? null;
                }
            } else {
                $province = $this->selectedProvince;
                $city = $this->formData['city'] ?? null;
            }

            $method = $this->payment_method;
            $isOffline = in_array($method, ['pos', 'transfer', 'cod']);
            $initialPaymentStatus = $isOffline 
                ? MarketSetting::getValue("orders.status_{$method}_payment", 'unpaid')
                : 'unpaid';
            $initialDeliveryStatus = MarketSetting::getValue("orders.status_{$method}_delivery", 'processing');

            $selectedMethod = $this->selectedShippingMethodId ? \Modules\Market\Entities\ShippingMethod::find($this->selectedShippingMethodId) : null;
            $selectedSlot = $this->selectedSlotId ? \Modules\Market\Entities\ShippingSlot::find($this->selectedSlotId) : null;

            $orderData = [
                'client_id' => $client->id,
                'checkout_form_id' => $this->form->id,
                'grand_total' => $this->totals['grand_total'] + $this->shippingCost,
                'total_items_price' => $this->totals['subtotal'],
                'total_discount' => $this->totals['discount'],
                'total_shipping_cost' => $this->shippingCost,
                'shipping_method' => $selectedMethod ? $selectedMethod->name : null,
                'payment_status' => $initialPaymentStatus,
                'delivery_status' => $initialDeliveryStatus,
                'payment_method' => $method,
            ];

            $orderData['shipping_address_json'] = [
                'province' => $province,
                'city' => $city,
                'recipient_name' => $this->formData['recipient_name'] ?? null,
                'recipient_mobile' => $this->formData['recipient_mobile'] ?? null,
                'recipient_national_code' => $this->formData['recipient_national_code'] ?? null,
                'delivery_date' => $this->selectedDeliveryDate,
                'delivery_slot' => $selectedSlot ? [
                    'start_time' => date('H:i', strtotime($selectedSlot->start_time)),
                    'end_time' => date('H:i', strtotime($selectedSlot->end_time)),
                ] : null,
            ];

            $order = Order::create($orderData);

            if ($this->selectedSlotId && $this->selectedDeliveryDate) {
                $booking = \Modules\Market\Entities\ShippingSlotBooking::firstOrCreate([
                    'shipping_slot_id' => $this->selectedSlotId,
                    'booking_date' => $this->selectedDeliveryDate,
                ]);
                $booking->increment('orders_count');
            }

            foreach ($freshCart as $itemArray) {
                if (empty($itemArray['vendor_id']) || empty($itemArray['vendor_product_id'])) {
                    throw new \Exception('خطای ساختاری: شناسه فروشنده یا محصول فروشنده برای آیتم سبد خرید یافت نشد.');
                }

                OrderItem::create([
                    'order_id' => (int) $order->id,
                    'vendor_product_id' => (int) $itemArray['vendor_product_id'],
                    'vendor_id' => (int) $itemArray['vendor_id'],
                    'product_title' => $itemArray['name'],
                    'quantity' => (int) $itemArray['quantity'],
                    'unit_price' => (float) $itemArray['price'],
                    'total_price' => (float) ($itemArray['quantity'] * $itemArray['price']),
                    'unit_tax' => 0,
                    'vendor_commission_rate' => 0,
                ]);

                $stockService->deduct($itemArray['variant_id'], $itemArray['quantity'], $itemArray['vendor_product_id'], (float) $itemArray['price']);
            }

            foreach ($this->formData as $key => $value) {
                if (!is_null($value) && $value !== '') {
                    OrderMeta::create([
                        'order_id' => $order->id,
                        'key' => $key,
                        'value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value
                    ]);
                }
            }

            $clientSyncService->sync($order, $client);

            return $order;
        });

        Session::forget('market_cart');
        $this->dispatch('notify', type: 'success', text: 'سفارش شما با موفقیت ثبت شد.');
        $this->dispatch('cartUpdated');

        return redirect()->route('market.checkout.process', ['order' => $order->id]);
    }

    protected function getDynamicRules(): array
    {
        $rules = [];
        foreach ($this->schema['fields'] ?? [] as $field) {
            $fieldRules = [];
            if (!empty($field['required'])) {
                $isRequired = false;
                if (empty($field['required_payment_methods'])) {
                    $isRequired = true;
                } elseif (in_array($this->payment_method, $field['required_payment_methods'])) {
                    $isRequired = true;
                }

                if ($isRequired) {
                    $fieldRules[] = 'required';
                }
            }

            if (!empty($field['validation'])) {
                $additionalRules = is_string($field['validation']) ? explode('|', $field['validation']) : $field['validation'];
                if (is_array($additionalRules)) {
                    $fieldRules = array_merge($fieldRules, $additionalRules);
                }
            }

            if (!empty($fieldRules)) {
                $rules['formData.' . $field['id']] = array_unique($fieldRules);
            }
        }
        $rules['payment_method'] = 'required|in:' . implode(',', array_keys($this->paymentMethods));
        return $rules;
    }

    protected function getDynamicValidationAttributes(): array
    {
        $attributes = [];
        foreach ($this->schema['fields'] ?? [] as $field) {
            $attributes['formData.' . $field['id']] = $field['label'] ?? $field['id'];
        }
        $attributes['payment_method'] = 'روش پرداخت';
        return $attributes;
    }

    public function getGroupedSchema(): array
    {
        $fields = collect($this->schema['fields'] ?? []);
        $groups = collect($this->schema['groups'] ?? []);

        return $groups->map(function($group) use ($fields) {
            if (empty($group['id'])) return null;

            return [
                'id' => $group['id'],
                'name' => $group['name'],
                'fields' => $fields->where('group', $group['id'])->values()->all()
            ];
        })->filter()->values()->all();
    }

    public function render()
    {
        $cart = $this->getFreshCartItems();

        if (empty($cart)) {
            return view('market::livewire.web.empty-cart')->layout('layouts.app');
        }

        if (!$this->form) {
            return view('market::livewire.web.no-checkout-form')->layout('layouts.app');
        }

        return view('market::livewire.web.checkout-page', [
            'groupedSchema' => $this->getGroupedSchema(),
            'cartItems' => $cart,
        ])->layout('layouts.web');
    }

    private function getPaymentMethods(): array
    {
        $methods = [];
        $settings = \Modules\Settings\Entities\Setting::all()->pluck('value', 'key')->toArray();
        $activeSystemMethods = json_decode($settings['active_payment_methods'] ?? '[]', true);
        if (!is_array($activeSystemMethods)) $activeSystemMethods = [];

        if (in_array('online', $activeSystemMethods)) {
            $gateways = ['zarinpal', 'zibal', 'behpardakht'];
            foreach ($gateways as $gateway) {
                if (($settings["{$gateway}_status"] ?? 'inactive') === 'active') {
                    $methods[$gateway] = ['type' => 'online', 'title' => $settings["{$gateway}_title"] ?? ucfirst($gateway), 'logo' => $settings["{$gateway}_logo"] ?? null];
                }
            }
        }
        if (in_array('pos', $activeSystemMethods) && ($settings['pos_status'] ?? 'inactive') === 'active') {
            $methods['pos'] = ['type' => 'offline', 'title' => 'پرداخت در محل (کارتخوان)', 'description' => $settings['pos_guidance'] ?? ''];
        }
        if (in_array('transfer', $activeSystemMethods) && ($settings['bank_transfer_status'] ?? 'inactive') === 'active') {
            $methods['transfer'] = ['type' => 'offline', 'title' => 'کارت به کارت / واریز به حساب', 'description' => $settings['bank_transfer_guidance'] ?? ''];
        }
        if (in_array('cod', $activeSystemMethods) && ($settings['cod_status'] ?? 'inactive') === 'active') {
            $methods['cod'] = ['type' => 'offline', 'title' => 'پرداخت در محل (نقدی)', 'description' => $settings['cod_guidance'] ?? ''];
        }
        return $methods;
    }

    public function selectAddress($addressId)
    {
        $client = Auth::guard('client')->user();
        if (!$client) return;

        $address = $client->addresses()->find($addressId);
        if (!$address) return;

        $this->selectedAddressId = $address->id;

        // Auto-fill matching fields in formData
        foreach ($this->schema['fields'] ?? [] as $field) {
            $fieldId = $field['id'];
            $fieldType = $field['type'] ?? '';
            $fieldLabel = $field['label'] ?? '';

            if ($fieldType === 'select-province-city') {
                $this->formData[$fieldId] = json_encode([
                    'province' => $address->province,
                    'city' => $address->city
                ], JSON_UNESCAPED_UNICODE);
                
                $this->selectedProvince = $address->province;
                $this->cities = ProvinceCity::getCities($address->province);
                $this->formData['city'] = $address->city;
            } elseif ($fieldId === 'address' || str_contains($fieldId, 'address') || str_contains($fieldLabel, 'آدرس') || str_contains($fieldLabel, 'نشانی')) {
                $this->formData[$fieldId] = $address->address;
            } elseif ($fieldType === 'postal-code' || $fieldId === 'postal_code' || str_contains($fieldId, 'postal') || str_contains($fieldId, 'postcode') || str_contains($fieldLabel, 'کد پستی')) {
                $this->formData[$fieldId] = $address->postal_code;
            }
        }
        $this->loadShippingMethods();
    }

    public function openNewAddressModal()
    {
        $this->resetNewAddressForm();
        $this->showNewAddressModal = true;
        $this->dispatch('initNewAddressMap', lat: $this->newLat, lng: $this->newLng);
    }

    public function closeNewAddressModal()
    {
        $this->showNewAddressModal = false;
        $this->resetNewAddressForm();
    }

    private function resetNewAddressForm()
    {
        $this->newTitle = '';
        $this->newProvince = '';
        $this->newCity = '';
        $this->newAddress = '';
        $this->newPostalCode = '';
        $this->newLat = 35.6892;
        $this->newLng = 51.3890;
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->resetErrorBag([
            'newTitle',
            'newProvince',
            'newCity',
            'newAddress',
            'newPostalCode',
        ]);
    }

    public function fetchNewAddressFromCoordinates($lat, $lng)
    {
        $this->newLat = $lat;
        $this->newLng = $lng;
        $geoData = [];

        if (interface_exists(\Modules\Market\App\Services\Map\MapServiceInterface::class) && app()->bound(\Modules\Market\App\Services\Map\MapServiceInterface::class)) {
            $mapService = app(\Modules\Market\App\Services\Map\MapServiceInterface::class);
            $geoData = $mapService->reverseGeocode($lat, $lng);
        }

        if (empty($geoData['address'])) {
            $geoData = $this->fallbackGeocode($lat, $lng);
        }

        if (!empty($geoData['address'])) {
            $this->newProvince = $geoData['province'] ?? $this->newProvince;
            $this->newCity = $geoData['city'] ?? $this->newCity;
            $this->newAddress = $this->sanitizeAddress($geoData['address'], $this->newProvince, $this->newCity);
            
            if (isset($geoData['data']['postal_code']) && !empty($geoData['data']['postal_code'])) {
                $this->newPostalCode = $geoData['data']['postal_code'];
            } elseif (isset($geoData['postal_code']) && !empty($geoData['postal_code'])) {
                $this->newPostalCode = $geoData['postal_code'];
            }
        }
    }

    protected function sanitizeAddress($address, $province, $city)
    {
        if (empty($address)) return '';
        
        $address = preg_replace('/^(ایران|iran)\s*[،,]\s*/iu', '', $address);

        if (!empty($province)) {
            $provinceClean = str_replace(['استان ', ' Province'], '', $province);
            $address = preg_replace('/^(' . preg_quote($province, '/') . '|' . preg_quote($provinceClean, '/') . '|استان\s+' . preg_quote($provinceClean, '/') . ')\s*[،,]\s*/iu', '', $address);
        }

        if (!empty($city)) {
            $cityClean = str_replace(['شهر ', ' City'], '', $city);
            $address = preg_replace('/^(' . preg_quote($city, '/') . '|' . preg_quote($cityClean, '/') . '|شهر\s+' . preg_quote($cityClean, '/') . ')\s*[،,]\s*/iu', '', $address);
        }

        $address = preg_replace('/\b\d{5}-?\d{5}\b/u', '', $address);
        $address = preg_replace('/^[،,\s]+|[،,\s]+$/u', '', $address);
        
        if (!empty($province)) {
            $provinceClean = str_replace(['استان ', ' Province'], '', $province);
            $address = preg_replace('/\s*[،,]\s*(' . preg_quote($province, '/') . '|' . preg_quote($provinceClean, '/') . '|استان\s+' . preg_quote($provinceClean, '/') . ')$/iu', '', $address);
        }
        if (!empty($city)) {
            $cityClean = str_replace(['شهر ', ' City'], '', $city);
            $address = preg_replace('/\s*[،,]\s*(' . preg_quote($city, '/') . '|' . preg_quote($cityClean, '/') . '|شهر\s+' . preg_quote($cityClean, '/') . ')$/iu', '', $address);
        }
        $address = preg_replace('/\s*[،,]\s*(ایران|iran)$/iu', '', $address);

        return preg_replace('/^[،,\s]+|[،,\s]+$/u', '', $address);
    }

    protected function fallbackGeocode($lat, $lng)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'User-Agent' => 'Laravel-CRM-Map-App'
            ])->timeout(5)->get("https://nominatim.openstreetmap.org/reverse", [
                'lat' => $lat,
                'lon' => $lng,
                'format' => 'json',
                'accept-language' => 'fa,en'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $addressData = $data['address'] ?? [];
                
                $province = $addressData['state'] ?? '';
                $province = str_replace(['استان ', ' Province'], '', $province);

                $city = $addressData['city'] ?? $addressData['town'] ?? $addressData['suburb'] ?? $addressData['village'] ?? '';

                $addressParts = [];
                if (!empty($addressData['road'])) {
                    $addressParts[] = $addressData['road'];
                }
                if (!empty($addressData['neighbourhood'])) {
                    $addressParts[] = $addressData['neighbourhood'];
                }
                if (!empty($addressData['suburb']) && $addressData['suburb'] !== $city) {
                    $addressParts[] = $addressData['suburb'];
                }
                if (!empty($addressData['borough'])) {
                    $addressParts[] = $addressData['borough'];
                }

                if (count($addressParts) < 2) {
                    $formattedAddress = $data['display_name'] ?? '';
                } else {
                    $formattedAddress = implode('، ', $addressParts);
                }

                $postcode = $addressData['postcode'] ?? '';
                if (!empty($postcode)) {
                    $postcode = str_replace('-', '', $postcode);
                }

                return [
                    'province' => $province,
                    'city' => $city,
                    'address' => $formattedAddress,
                    'formatted' => $formattedAddress,
                    'postal_code' => $postcode,
                    'data' => $data
                ];
            }
        } catch (\Exception $e) {
            // Silence fallback errors
        }

        return [
            'province' => '',
            'city' => '',
            'address' => '',
            'formatted' => ''
        ];
    }

    public function saveNewAddress()
    {
        $client = Auth::guard('client')->user();
        if (!$client) return;

        $this->validate([
            'newTitle' => 'required|string|max:100',
            'newProvince' => 'required|string|max:100',
            'newCity' => 'required|string|max:100',
            'newAddress' => 'required|string',
            'newPostalCode' => 'required|string|max:10',
        ], [], [
            'newTitle' => 'عنوان آدرس',
            'newProvince' => 'استان',
            'newCity' => 'شهر',
            'newAddress' => 'نشانی دقیق',
            'newPostalCode' => 'کد پستی',
        ]);

        $isDefault = $client->addresses()->count() === 0;

        $newAddr = $client->addresses()->create([
            'title' => $this->newTitle,
            'province' => $this->newProvince,
            'city' => $this->newCity,
            'address' => $this->newAddress,
            'postal_code' => $this->newPostalCode,
            'lat' => $this->newLat,
            'lng' => $this->newLng,
            'is_default' => $isDefault,
        ]);

        $this->addresses = $client->addresses()->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->get();

        $this->selectAddress($newAddr->id);

        $this->dispatch('notify', type: 'success', text: 'آدرس جدید با موفقیت اضافه و انتخاب شد.');
        $this->closeNewAddressModal();
    }

    public function updatedFormData($value, $key)
    {
        $provinceCityFieldId = $this->findFieldIdByType('select-province-city');
        if ($key === $provinceCityFieldId || $key === 'city' || $key === 'province') {
            $this->loadShippingMethods();
        }
    }

    public function loadShippingMethods()
    {
        $this->shippingMethods = [];
        $this->selectedShippingMethodId = null;
        $this->shippingCost = 0;
        $this->availableSlots = [];
        $this->selectedSlotId = null;
        $this->selectedDeliveryDate = null;

        // Extract province and city
        $provinceCityFieldId = $this->findFieldIdByType('select-province-city');
        $province = null;
        $city = null;

        if ($provinceCityFieldId && isset($this->formData[$provinceCityFieldId])) {
            $val = $this->formData[$provinceCityFieldId];
            $decoded = is_string($val) ? json_decode($val, true) : $val;
            if (is_array($decoded)) {
                $province = $decoded['province'] ?? null;
                $city = $decoded['city'] ?? null;
            }
        } else {
            $province = $this->selectedProvince;
            $city = $this->formData['city'] ?? null;
        }

        if (!$province) {
            return;
        }

        $freshCart = $this->getFreshCartItems();

        // Find available methods
        $shippingService = app(\Modules\Market\App\Services\ShippingService::class);
        $allMethods = \Modules\Market\Entities\ShippingMethod::where('is_active', true)->orderBy('sort_order', 'asc')->get();

        foreach ($allMethods as $method) {
            $cost = $shippingService->calculateShippingCost($method->id, $province, $city, $freshCart, $this->totals['grand_total']);
            
            if ($cost === null) {
                continue;
            }

            $hasSlots = $method->slots()->exists();

            $this->shippingMethods[] = [
                'id' => $method->id,
                'name' => $method->name,
                'driver' => $method->driver,
                'cost' => $cost,
                'has_slots' => $hasSlots,
            ];
        }
    }

    public function updatedSelectedShippingMethodId($value)
    {
        $this->selectedSlotId = null;
        $this->selectedDeliveryDate = null;
        $this->availableSlots = [];

        if (!$value) {
            $this->shippingCost = 0;
            return;
        }

        $method = collect($this->shippingMethods)->firstWhere('id', $value);
        $this->shippingCost = $method ? $method['cost'] : 0;

        $this->loadAvailableSlots($value);
    }

    public function loadAvailableSlots($methodId)
    {
        $method = \Modules\Market\Entities\ShippingMethod::find($methodId);
        if (!$method) return;

        // Extract province and city
        $provinceCityFieldId = $this->findFieldIdByType('select-province-city');
        $province = null;
        $city = null;

        if ($provinceCityFieldId && isset($this->formData[$provinceCityFieldId])) {
            $val = $this->formData[$provinceCityFieldId];
            $decoded = is_string($val) ? json_decode($val, true) : $val;
            if (is_array($decoded)) {
                $province = $decoded['province'] ?? null;
                $city = $decoded['city'] ?? null;
            }
        } else {
            $province = $this->selectedProvince;
            $city = $this->formData['city'] ?? null;
        }

        $slots = $method->slots()
            ->where(function($q) use ($province, $city) {
                $q->whereNull('states')
                  ->orWhere(function($sub) use ($province, $city) {
                      $sub->whereJsonContains('states', $province)
                          ->where(function($sub2) use ($city) {
                              $sub2->whereNull('cities')
                                   ->orWhereJsonContains('cities', $city);
                          });
                  });
            })
            ->get();

        if ($slots->isEmpty()) {
            return;
        }

        // Generate dates for the next 7 days
        $this->availableSlots = [];
        for ($i = 1; $i <= 7; $i++) {
            $date = now()->addDays($i);
            $dayOfWeek = $date->dayOfWeek; // 0 (Sunday) to 6 (Saturday) in Carbon
            
            $daySlots = $slots->filter(function ($slot) use ($dayOfWeek) {
                return is_array($slot->days) && in_array($dayOfWeek, $slot->days);
            });
            
            foreach ($daySlots as $slot) {
                // Check capacity
                $booking = \Modules\Market\Entities\ShippingSlotBooking::where('shipping_slot_id', $slot->id)
                    ->where('booking_date', $date->format('Y-m-d'))
                    ->first();
                
                $bookedCount = $booking ? $booking->orders_count : 0;
                if ($bookedCount < $slot->capacity) {
                    $jalaliDate = \Morilog\Jalali\Jalalian::fromCarbon($date)->format('Y/m/d');
                    $daysOfWeekNames = [
                        0 => 'یکشنبه',
                        1 => 'دوشنبه',
                        2 => 'سه‌شنبه',
                        3 => 'چهارشنبه',
                        4 => 'پنجشنبه',
                        5 => 'جمعه',
                        6 => 'شنبه'
                    ];
                    $dayName = $daysOfWeekNames[$dayOfWeek] ?? '';

                    $this->availableSlots[] = [
                        'slot_id' => $slot->id,
                        'date' => $date->format('Y-m-d'),
                        'jalali_date' => $jalaliDate,
                        'day_name' => $dayName,
                        'start_time' => date('H:i', strtotime($slot->start_time)),
                        'end_time' => date('H:i', strtotime($slot->end_time)),
                        'remaining' => $slot->capacity - $bookedCount,
                    ];
                }
            }
        }
    }

    public function selectSlot($slotId, $date)
    {
        $this->selectedSlotId = $slotId;
        $this->selectedDeliveryDate = $date;
    }

    public function getCurrencyLabel(): string
    {
        $currency = \Modules\Market\Entities\MarketSetting::getValue('general.currency', 'toman');
        return $currency === 'rial' ? 'ریال' : 'تومان';
    }
}

