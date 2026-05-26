<?php

namespace Modules\Market\App\Livewire\User;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Market\App\Services\OrderService;
use Modules\Market\App\Services\StockService;
use Modules\Settings\Entities\Setting;

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

    public $provinces = [];
    public $cities = [];
    public $paymentGateways = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'mobile' => 'required|iran_mobile',
            // 'province_id' => 'required|exists:provinces,id',
            // 'city_id' => 'required|exists:cities,id',
            'address' => 'required|string|max:1000',
            'payment_method' => 'required|in:' . implode(',', array_keys($this->getAvailablePaymentMethods())),
        ];
    }

    protected $messages = [
        'name.required' => 'نام و نام خانوادگی الزامی است.',
        'mobile.required' => 'شماره موبایل الزامی است.',
        'mobile.iran_mobile' => 'شماره موبایل معتبر نیست.',
        'province_id.required' => 'انتخاب استان الزامی است.',
        'city_id.required' => 'انتخاب شهر الزامی است.',
        'address.required' => 'آدرس دقیق الزامی است.',
        'payment_method.required' => 'انتخاب روش پرداخت الزامی است.',
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

        $this->provinces = [];
        $this->cities = collect();
        $this->paymentGateways = $this->getAvailablePaymentMethods();

        if (auth()->check()) {
            $user = auth()->user();
            $this->name = $user->name;
            $this->mobile = $user->mobile;

            // 💡 پیدا کردن اولین پروفایل مشتری متصل به کاربر
            $client = $user->clients()->first();

            if ($client) {
                // 💡 پیدا کردن آخرین سفارش از طریق پروفایل مشتری
                $lastOrder = $client->orders()->latest()->first();
                if ($lastOrder && $lastOrder->shipping_address_json) {
                    $shippingAddress = $lastOrder->shipping_address_json;
                    $this->province_id = $shippingAddress['province_id'] ?? null;
                    if($this->province_id) {
                       $this->updatedProvinceId($this->province_id);
                    }
                    $this->city_id = $shippingAddress['city_id'] ?? null;
                    $this->address = $shippingAddress['address'] ?? '';
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
        $this->cities = collect();
        $this->city_id = null;
    }

    public function submit(OrderService $orderService, StockService $stockService)
    {
        $this->validate();

        $user = auth()->user();
        // 💡 پیدا کردن اولین پروفایل مشتری متصل به کاربر
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
            $order = $orderService->create([
                'client_id' => $client->id, // 💡 استفاده از client_id
                'name' => $this->name,
                'mobile' => $this->mobile,
                'payment_method' => $this->payment_method,
                'grand_total' => $this->totalAmount,
                'total_items_price' => $this->totalAmount,
                'items' => $this->cartItems,
                'shipping_address_json' => [
                    'name' => $this->name,
                    'mobile' => $this->mobile,
                    'province_id' => $this->province_id,
                    'city_id' => $this->city_id,
                    'address' => $this->address,
                ],
            ]);

            // 3. Reserve stock
            $stockService->reserveForOrder($order);

            DB::commit();

            // 4. Redirect to payment or show success message
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

    public function render()
    {
        return view('market::livewire.user.checkout-modal');
    }
}
