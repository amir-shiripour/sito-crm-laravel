<?php

namespace Modules\Market\App\Livewire\User;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Market\App\Services\OrderService;
use Modules\Market\App\Services\StockService;
use Modules\Market\App\Services\ShippingService;
use Modules\Market\Entities\ShippingMethod;
use Modules\Market\Entities\ShippingSlot;
use Modules\Market\Entities\ShippingSlotBooking;
use Modules\Settings\Entities\Setting;
use App\Helpers\ProvinceCity;

class CheckoutModal extends Component
{
    public bool $isOpen = false;
    public bool $isFullPage = false;
    public $cartItems = [];
    public $totalAmount = 0;

    // Form fields
    public string $name = '';
    public string $mobile = '';
    public string $address = '';
    public $province_id = null;
    public $city_id = null;
    public string $payment_method = 'online';

    // Shipping Fields
    public $shippingMethods = [];
    public $selectedShippingMethodId = null;
    public $shippingCost = 0;
    public $availableSlots = [];
    public $selectedSlotId = null;
    public $selectedDeliveryDate = null;

    public $provinces = [];
    public $cities = [];
    public $paymentGateways = [];

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'mobile' => 'required|iran_mobile',
            'province_id' => 'required|string',
            'city_id' => 'required|string',
            'address' => 'required|string|max:1000',
            'payment_method' => 'required|in:' . implode(',', array_keys($this->getAvailablePaymentMethods())),
        ];

        if (!empty($this->shippingMethods)) {
            $rules['selectedShippingMethodId'] = 'required|exists:market_shipping_methods,id';
        }

        $method = $this->selectedShippingMethodId ? ShippingMethod::find($this->selectedShippingMethodId) : null;
        if ($method && $method->slots()->exists()) {
            $rules['selectedSlotId'] = 'required|exists:market_shipping_slots,id';
            $rules['selectedDeliveryDate'] = 'required|date';
        }

        return $rules;
    }

    protected $messages = [
        'name.required' => 'نام و نام خانوادگی الزامی است.',
        'mobile.required' => 'شماره موبایل الزامی است.',
        'mobile.iran_mobile' => 'شماره موبایل معتبر نیست.',
        'province_id.required' => 'انتخاب استان الزامی است.',
        'city_id.required' => 'انتخاب شهر الزامی است.',
        'address.required' => 'آدرس دقیق الزامی است.',
        'payment_method.required' => 'انتخاب روش پرداخت الزامی است.',
        'selectedShippingMethodId.required' => 'انتخاب روش ارسال الزامی است.',
        'selectedSlotId.required' => 'انتخاب بازه زمانی تحویل الزامی است.',
        'selectedDeliveryDate.required' => 'انتخاب تاریخ تحویل الزامی است.',
    ];

    public function mount($isFullPage = false)
    {
        $this->isFullPage = $isFullPage;
        if ($this->isFullPage) {
            $this->isOpen = true;
            $cart = session('cart', ['items' => [], 'total' => 0]);
            $this->cartItems = $cart['items'];
            $this->totalAmount = $cart['total'];
        }

        $this->provinces = ProvinceCity::getProvinces();
        $this->cities = collect();
        $this->paymentGateways = $this->getAvailablePaymentMethods();

        if (auth()->check()) {
            $user = auth()->user();
            $this->name = $user->name;
            $this->mobile = $user->mobile;

            // پیدا کردن اولین پروفایل مشتری متصل به کاربر
            $client = $user->clients()->first();

            if ($client) {
                // پیدا کردن آخرین سفارش از طریق پروفایل مشتری
                $lastOrder = $client->orders()->latest()->first();
                if ($lastOrder && $lastOrder->shipping_address_json) {
                    $shippingAddress = $lastOrder->shipping_address_json;
                    $this->province_id = $shippingAddress['province_id'] ?? null;
                    if($this->province_id) {
                       $this->updatedProvinceId($this->province_id);
                    }
                    $this->city_id = $shippingAddress['city_id'] ?? null;
                    $this->address = $shippingAddress['address'] ?? '';
                    if ($this->city_id) {
                        $this->loadShippingMethods();
                    }
                }
            }
        }
    }

    private function getAvailablePaymentMethods(): array
    {
        $methods = [];
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        if (($settings['active_payment_methods'] ?? null) && is_string($settings['active_payment_methods'])) {
            $activeMethods = json_decode($settings['active_payment_methods'], true);
        } else {
            $activeMethods = $settings['active_payment_methods'] ?? [];
        }


        if (in_array('online', $activeMethods)) {
            $defaultGateway = $settings['default_payment_gateway'] ?? null;
            if ($defaultGateway && ($settings[$defaultGateway.'_status'] ?? 'inactive') === 'active') {
                $methods['online'] = [
                    'title' => 'پرداخت اینترنتی',
                    'description' => 'اتصال به درگاه امن بانکی',
                    'icon' => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>'
                ];
            }
        }

        if (in_array('pos', $activeMethods)) {
             $methods['pos'] = [
                'title' => 'پرداخت در محل (با کارتخوان)',
                'description' => $settings['pos_guidance'] ?? 'پرداخت در زمان تحویل کالا',
                'icon' => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'
            ];
        }

        if (in_array('transfer', $activeMethods)) {
            $methods['transfer'] = [
                'title' => 'کارت به کارت / انتقال بانکی',
                'description' => $settings['bank_transfer_guidance'] ?? 'واریز به حساب و بارگذاری فیش',
                'icon' => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>'
            ];
        }

        return $methods;
    }

    #[On('open-checkout-modal')]
    public function openModal($cart)
    {
        if ($this->isFullPage) {
            return;
        }
        $this->cartItems = $cart['items'];
        $this->totalAmount = $cart['total'];
        $this->isOpen = true;
        $this->dispatch('checkout-modal-opened');
    }

    public function closeModal()
    {
        if ($this->isFullPage) {
            return;
        }
        $this->isOpen = false;
        $this->resetErrorBag();
        $this->dispatch('checkout-modal-closed');
    }

    public function updatedProvinceId($value)
    {
        $this->cities = ProvinceCity::getCities($value);
        $this->city_id = null;
        $this->loadShippingMethods();
    }

    public function updatedCityId($value)
    {
        $this->loadShippingMethods();
    }

    public function loadShippingMethods()
    {
        $this->shippingMethods = [];
        $this->selectedShippingMethodId = null;
        $this->shippingCost = 0;
        $this->availableSlots = [];
        $this->selectedSlotId = null;
        $this->selectedDeliveryDate = null;

        if (!$this->province_id) {
            return;
        }

        // Find available methods
        $shippingService = app(ShippingService::class);
        $allMethods = ShippingMethod::where('is_active', true)->orderBy('sort_order', 'asc')->get();

        foreach ($allMethods as $method) {
            $cost = $shippingService->calculateShippingCost($method->id, $this->province_id, $this->city_id, $this->cartItems, $this->totalAmount);
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
        $method = ShippingMethod::find($methodId);
        if (!$method) return;

        $slots = $method->slots()
            ->where(function($q) {
                $q->whereNull('state')
                  ->orWhere(function($sub) {
                      $sub->where('state', $this->province_id)
                          ->where(function($sub2) {
                              $sub2->whereNull('city')
                                   ->orWhere('city', $this->city_id);
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
            
            $daySlots = $slots->where('day_of_week', $dayOfWeek);
            foreach ($daySlots as $slot) {
                // Check capacity
                $booking = ShippingSlotBooking::where('shipping_slot_id', $slot->id)
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

    public function submit(OrderService $orderService, StockService $stockService)
    {
        $this->validate();

        $user = auth()->user();
        $client = $user->clients()->first();

        if (!$client) {
            $this->addError('submit', 'پروفایل مشتری برای کاربر شما تعریف نشده است.');
            return;
        }

        $unavailableProducts = $stockService->checkAvailabilityForCity($this->cartItems, (int) $this->city_id);

        if (!empty($unavailableProducts)) {
            $productNames = implode('، ', array_map(fn($p) => $p['name'], $unavailableProducts));
            $this->addError('stock', "موجودی برخی از محصولات برای شهر انتخابی شما کافی نیست: {$productNames}");
            return;
        }

        DB::beginTransaction();
        try {
            $selectedMethod = $this->selectedShippingMethodId ? ShippingMethod::find($this->selectedShippingMethodId) : null;
            $selectedSlot = $this->selectedSlotId ? ShippingSlot::find($this->selectedSlotId) : null;

            $order = $orderService->create([
                'client_id' => $client->id,
                'name' => $this->name,
                'mobile' => $this->mobile,
                'payment_method' => $this->payment_method,
                'grand_total' => $this->totalAmount + $this->shippingCost,
                'total_items_price' => $this->totalAmount,
                'total_shipping_cost' => $this->shippingCost,
                'shipping_method' => $selectedMethod ? $selectedMethod->name : null,
                'items' => $this->cartItems,
                'shipping_address_json' => [
                    'name' => $this->name,
                    'mobile' => $this->mobile,
                    'province_id' => $this->province_id,
                    'city_id' => $this->city_id,
                    'address' => $this->address,
                    'delivery_date' => $this->selectedDeliveryDate,
                    'delivery_slot' => $selectedSlot ? [
                        'start_time' => date('H:i', strtotime($selectedSlot->start_time)),
                        'end_time' => date('H:i', strtotime($selectedSlot->end_time)),
                    ] : null,
                ],
            ]);

            // Reserve stock
            $stockService->reserveForOrder($order);

            // Increment capacity booking count
            if ($this->selectedSlotId && $this->selectedDeliveryDate) {
                $booking = ShippingSlotBooking::firstOrCreate([
                    'shipping_slot_id' => $this->selectedSlotId,
                    'booking_date' => $this->selectedDeliveryDate,
                ]);
                $booking->increment('orders_count');
            }

            DB::commit();

            if (!$this->isFullPage) {
                $this->closeModal();
            }
            $this->dispatch('cart-cleared');
            return $this->redirect()->to(route('market.checkout.process', $order));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('submit', 'خطایی در هنگام ثبت سفارش رخ داد: ' . $e->getMessage());
        }
    }

    public function getCurrencyLabel(): string
    {
        $currency = \Modules\Market\Entities\MarketSetting::getValue('general.currency', 'toman');
        return $currency === 'rial' ? 'ریال' : 'تومان';
    }

    public function render()
    {
        return view('market::livewire.user.checkout-modal');
    }
}
