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

        $this->paymentMethods = $this->getPaymentMethods();
        if (!empty($this->paymentMethods)) {
            $this->payment_method = array_key_first($this->paymentMethods);
        }

        $this->calculateTotals($cart);
    }

    public function updatedSelectedProvince($provinceName)
    {
        $this->cities = ProvinceCity::getCities($provinceName);
        $this->formData['city'] = null;
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

        foreach ($sessionCart as $item) {
            if (empty($item['variant_id']) || empty($item['vendor_product_id'])) {
                continue;
            }

            $variant = ProductVariant::with('masterProduct.category')->find($item['variant_id']);
            $vp = VendorProduct::find($item['vendor_product_id']);

            if ($variant && $vp) {
                $finalPrice = $vp->discount_price > 0 ? $vp->discount_price : $vp->price;

                $refreshedCart[] = [
                    'variant_id' => (int) $variant->id,
                    'vendor_product_id' => (int) $vp->id,
                    'vendor_id' => (int) $vp->vendor_id,
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $finalPrice,
                    'base_price' => (float) $vp->price,
                    'name' => $variant->masterProduct->title,
                    'image' => $variant->masterProduct->main_image_url,
                    'product_id' => (int) $variant->master_product_id,
                    'category_id' => (int) $variant->masterProduct->category_id,
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

        $this->validate($this->getDynamicRules());

        $client = Auth::guard('client')->user();
        if (!$client) {
            $this->dispatch('notify', type: 'error', text: 'برای ثبت سفارش باید ابتدا وارد شوید.');
            return;
        }

        $freshCart = $this->getFreshCartItems();
        if (empty($freshCart)) {
            $this->dispatch('notify', type: 'error', text: 'سبد خرید شما خالی است.');
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

            $orderData = [
                'client_id' => $client->id,
                'checkout_form_id' => $this->form->id,
                'grand_total' => $this->totals['grand_total'],
                'total_items_price' => $this->totals['subtotal'],
                'total_discount' => $this->totals['discount'],
                'payment_status' => 'unpaid',
                'delivery_status' => 'processing',
                'payment_method' => $this->payment_method,
            ];

            $orderData['shipping_address_json'] = [
                'province' => $province,
                'city' => $city,
                'recipient_name' => $this->formData['recipient_name'] ?? null,
                'recipient_mobile' => $this->formData['recipient_mobile'] ?? null,
                'recipient_national_code' => $this->formData['recipient_national_code'] ?? null,
            ];

            $order = Order::create($orderData);

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

                $stockService->deduct($itemArray['variant_id'], $itemArray['quantity'], $itemArray['vendor_product_id']);
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
                $fieldRules[] = 'required';
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
        return $methods;
    }
}
