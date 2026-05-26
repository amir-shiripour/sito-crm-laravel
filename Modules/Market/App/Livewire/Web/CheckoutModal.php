<?php

namespace Modules\Market\App\Livewire\Web;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
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
                $fieldRules[] = 'required';
            }
            if (!empty($field['validation']) && is_array($field['validation'])) {
                $fieldRules = array_merge($fieldRules, $field['validation']);
            }
            if (!empty($fieldRules)) {
                $rules['formData.' . $field['id']] = $fieldRules;
            }
        }
        $rules['payment_method'] = 'required|in:' . implode(',', array_keys($this->paymentMethods));


        return $rules;
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

    public function submit(OrderService $orderService, StockService $stockService, ClientSyncService $clientSyncService)
    {
        $this->validate($this->getDynamicRules());

        $user = auth()->user();
        $client = $user->clients()->first();

        if (!$client) {
            $this->addError('submit', 'پروفایل مشتری برای کاربر شما تعریف نشده است.');
            return;
        }

        $order = DB::transaction(function () use ($client, $clientSyncService, $stockService, $orderService) {


            $order = $orderService->create([
                'client_id' => $client->id,
                'checkout_form_id' => $this->form->id,
                'name' => $this->formData['full_name'] ?? $client->name,
                'mobile' => $this->formData['phone'] ?? $client->mobile,
                'payment_method' => $this->payment_method,
                'grand_total' => $this->totalAmount,
                'total_items_price' => $this->totalAmount,
                'items' => $this->cartItems->map(fn($item) => ['id' => $item['variant_id'], 'quantity' => $item['quantity'], 'price' => $item['price'], 'name' => $item['name']])->toArray(),
                'shipping_address_json' => $this->formData,
            ]);

            $systemFieldIds = array_keys(CheckoutForm::getSystemFields());
            foreach ($this->formData as $key => $value) {
                if (!in_array($key, $systemFieldIds) && !is_null($value)) {
                    OrderMeta::create(['order_id' => $order->id, 'key' => $key, 'value' => $value]);
                }
            }

            $stockService->reserveForOrder($order);
            $clientSyncService->sync($order, $client);

            return $order;
        });

        $this->closeModal();
        $this->dispatch('cart-cleared');
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

    public function render()
    {
        return view('market::livewire.web.checkout-modal', [
            'groupedSchema' => $this->form ? $this->getGroupedSchema() : [],
        ]);
    }
}
