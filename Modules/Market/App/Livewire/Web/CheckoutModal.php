<?php

namespace Modules\Market\App\Livewire\Web;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Market\App\Models\CheckoutForm;
use Modules\Market\App\Models\Order;
use Modules\Market\App\Models\OrderItem;
use Modules\Market\App\Models\OrderMeta;
use Modules\Market\App\Services\ClientSyncService;
use Modules\Market\App\Services\OrderService;
use Modules\Market\App\Services\StockService;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\VendorProduct;
use Modules\Market\Entities\ShippingMethod;
use Modules\Market\Entities\ShippingSlot;
use Modules\Market\Entities\ShippingSlotBooking;
use App\Helpers\ProvinceCity;

class CheckoutModal extends Component
{
    public bool $isOpen = false;
    public $cartItems = [];
    public $totalAmount = 0;

    // Dynamic Form Properties
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

    public string $payment_method = '';
    public array $paymentMethods = [];

    protected $listeners = [
        'open-checkout-modal' => 'openModal'
    ];

    public function mount()
    {
        $this->paymentMethods = $this->getPaymentMethods();
        if (!empty($this->paymentMethods)) {
            $this->payment_method = array_key_first($this->paymentMethods);
        }

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

    #[On('open-checkout-modal')]
    public function openModal()
    {
        $clientSyncService = app(ClientSyncService::class);
        $this->reset(['formData', 'form', 'schema']);
        $this->loadCartData();

        if ($this->cartItems->isEmpty()) {
            $this->dispatch('notify', type: 'error', text: 'سبد خرید شما خالی است.');
            return;
        }

        $this->loadForm();

        if ($this->form) {
            $this->schema = $this->form->getSchema();
            $this->initializeData($clientSyncService);
            $this->loadShippingMethods();
        }

        $this->isOpen = true;
        $this->dispatch('checkout-modal-opened');
    }

    private function loadCartData()
    {
        $sessionCart = Session::get('market_cart', []);
        $refreshedCart = [];
        $subtotal = 0;
        $discount = 0;

        foreach ($sessionCart as $key => $item) {
            $variant = ProductVariant::with('masterProduct.category')->find($item['variant_id']);
            $vp = VendorProduct::find($item['vendor_product_id']);

            if ($variant && $vp) {
                $finalPrice = $vp->discount_price > 0 ? $vp->discount_price : $vp->price;
                $discountAmount = $vp->discount_price > 0 ? ($vp->price - $vp->discount_price) : 0;

                $refreshedCart[$key] = [
                    'variant_id' => $item['variant_id'],
                    'vendor_product_id' => $item['vendor_product_id'],
                    'quantity' => $item['quantity'],
                    'product_id' => $variant->master_product_id,
                    'category_id' => $variant->masterProduct->category_id,
                    'price' => $finalPrice,
                    'name' => $variant->masterProduct->title,
                    'image' => $variant->masterProduct->main_image_url,
                ];

                $subtotal += $vp->price * $item['quantity'];
                $discount += $discountAmount * $item['quantity'];
            }
        }

        $this->cartItems = collect($refreshedCart);
        $this->totalAmount = $subtotal - $discount;
    }

    private function loadForm(): void
    {
        $productIds = $this->cartItems->pluck('product_id')->all();
        $categoryIds = $this->cartItems->pluck('category_id')->filter()->unique()->all();

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

        $defaults = collect($this->schema['fields'] ?? [])
            ->mapWithKeys(fn($field) => [$field['id'] => ''])
            ->all();

        $hydratedData = [];
        if ($client && $this->form) {
            $hydratedData = $clientSyncService->hydrate($client, $this->form->key);
        }

        $this->formData = array_merge($defaults, $hydratedData);
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
        }

        if (!$province) {
            return;
        }

        // Find available methods
        $shippingService = app(\Modules\Market\App\Services\ShippingService::class);
        $allMethods = \Modules\Market\Entities\ShippingMethod::where('is_active', true)->orderBy('sort_order', 'asc')->get();

        foreach ($allMethods as $method) {
            $cost = $shippingService->calculateShippingCost($method->id, $province, $city, $this->cartItems->toArray(), $this->totalAmount);
            
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

    public function submit(StockService $stockService, ClientSyncService $clientSyncService)
    {
        $user = auth()->user();
        $client = $user ? $user->clients()->first() : null;

        if (!$client) {
            $this->dispatch('notify', type: 'error', text: 'برای ثبت سفارش باید ابتدا وارد شوید.');
            return;
        }

        // Geolocation ordering validation
        if (MarketSetting::getValue('orders.enable_geolocation_ordering', false)) {
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
            }

            if (!$city) {
                $this->dispatch('notify', type: 'error', text: 'لطفاً موقعیت مکانی خود را انتخاب کنید.');
                return;
            }

            foreach ($this->cartItems as $item) {
                $vendorProduct = VendorProduct::find($item['vendor_product_id']);
                if ($vendorProduct) {
                    $vendorCity = $vendorProduct->vendor->addresses()->where('is_default', true)->first()?->city 
                        ?? $vendorProduct->vendor->addresses()->first()?->city;
                    if ($vendorCity !== $city) {
                        $this->dispatch('notify', type: 'error', text: 'محصول ' . $item['name'] . ' در موقعیت مکانی انتخابی شما موجود نیست.');
                        return;
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

        $order = DB::transaction(function () use ($client, $clientSyncService, $stockService) {
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
            }

            $method = $this->payment_method;
            $isOffline = in_array($method, ['pos', 'transfer', 'cod']);
            $initialPaymentStatus = $isOffline 
                ? MarketSetting::getValue("orders.status_{$method}_payment", 'unpaid')
                : 'unpaid';
            $initialDeliveryStatus = MarketSetting::getValue("orders.status_{$method}_delivery", 'processing');

            $selectedMethod = $this->selectedShippingMethodId ? \Modules\Market\Entities\ShippingMethod::find($this->selectedShippingMethodId) : null;
            $selectedSlot = $this->selectedSlotId ? \Modules\Market\Entities\ShippingSlot::find($this->selectedSlotId) : null;

            // Calculate subtotal and discount
            $subtotal = 0;
            $discount = 0;
            foreach ($this->cartItems as $item) {
                $vp = VendorProduct::find($item['vendor_product_id']);
                if ($vp) {
                    $subtotal += $vp->price * $item['quantity'];
                    $discount += ($vp->price - $item['price']) * $item['quantity'];
                }
            }

            $orderData = [
                'client_id' => $client->id,
                'checkout_form_id' => $this->form->id,
                'grand_total' => $this->totalAmount + $this->shippingCost,
                'total_items_price' => $subtotal,
                'total_discount' => $discount,
                'total_shipping_cost' => $this->shippingCost,
                'shipping_method' => $selectedMethod ? $selectedMethod->name : null,
                'payment_status' => $initialPaymentStatus,
                'delivery_status' => $initialDeliveryStatus,
                'payment_method' => $method,
            ];

            $orderData['shipping_address_json'] = array_merge($this->formData, [
                'province' => $province,
                'city' => $city,
                'delivery_date' => $this->selectedDeliveryDate,
                'delivery_slot' => $selectedSlot ? [
                    'start_time' => date('H:i', strtotime($selectedSlot->start_time)),
                    'end_time' => date('H:i', strtotime($selectedSlot->end_time)),
                ] : null,
            ]);

            $order = Order::create($orderData);

            if ($this->selectedSlotId && $this->selectedDeliveryDate) {
                $booking = \Modules\Market\Entities\ShippingSlotBooking::firstOrCreate([
                    'shipping_slot_id' => $this->selectedSlotId,
                    'booking_date' => $this->selectedDeliveryDate,
                ]);
                $booking->increment('orders_count');
            }

            foreach ($this->cartItems as $item) {
                $vp = VendorProduct::find($item['vendor_product_id']);
                OrderItem::create([
                    'order_id' => (int) $order->id,
                    'vendor_product_id' => (int) $item['vendor_product_id'],
                    'vendor_id' => (int) $vp->vendor_id,
                    'product_title' => $item['name'],
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (float) $item['price'],
                    'total_price' => (float) ($item['quantity'] * $item['price']),
                    'unit_tax' => 0,
                    'vendor_commission_rate' => 0,
                ]);

                $stockService->deduct($item['variant_id'], $item['quantity'], $item['vendor_product_id'], (float) $item['price']);
            }

            // OrderMeta fields
            $systemFieldIds = array_keys(CheckoutForm::getSystemFields());
            foreach ($this->formData as $key => $value) {
                if (!in_array($key, $systemFieldIds) && !is_null($value) && $value !== '') {
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

        $this->closeModal();
        $this->dispatch('cart-cleared');
        $this->dispatch('notify', type: 'success', text: 'سفارش شما با موفقیت ثبت شد.');
        return $this->redirect()->to(route('market.checkout.process', $order));
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
        return $methods;
    }

    public function getGroupedSchema(): array
    {
        $fields = collect($this->schema['fields'] ?? []);
        $groups = collect($this->schema['groups'] ?? []);

        return $groups->map(function($group) use ($fields) {
            if (empty($group['id'])) return null;
            return ['id' => $group['id'], 'name' => $group['name'], 'fields' => $fields->where('group', $group['id'])->values()->all()];
        })->filter()->values()->all();
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetErrorBag();
        $this->dispatch('checkout-modal-closed');
    }

    public function render()
    {
        return view('market::livewire.web.checkout-modal', [
            'groupedSchema' => $this->form ? $this->getGroupedSchema() : [],
        ]);
    }
}
