<?php

namespace Modules\Market\App\Livewire\User;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Clients\Entities\Client;
use Modules\Market\App\Models\Order;
use Modules\Market\App\Models\OrderItem;
use Modules\Market\App\Models\OrderMeta;
use Modules\Market\App\Models\CheckoutForm;
use Modules\Market\Entities\VendorProduct;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\App\Services\StockService;
use App\Helpers\ProvinceCity;

class OrderForm extends Component
{
    public ?Order $order = null;
    public bool $isEdit = false;

    // Order Fields
    public $clientId;
    public $formId;
    public $payment_method = 'zibal';
    public $payment_status = 'unpaid';
    public $delivery_status = 'processing';
    
    public array $shipping_address = [
        'province' => '',
        'city' => '',
        'address' => '',
        'recipient_name' => '',
        'recipient_mobile' => '',
        'recipient_national_code' => '',
    ];

    // Items list
    public array $items = []; // array of ['vendor_product_id', 'title', 'price', 'quantity', 'stock']

    // Dynamic metadata fields (based on Selected Form)
    public array $formData = [];
    public array $checkoutFields = [];

    // Search and Lists
    public string $searchClient = '';
    public string $searchProduct = '';
    public array $clientsList = [];
    public array $productsList = [];
    public array $checkoutFormsList = [];
    public array $provinces = [];
    public array $cities = [];

    // Client Quick Creation inline
    public bool $showQuickClientModal = false;
    public array $newClient = [
        'full_name' => '',
        'phone' => '',
        'email' => '',
        'national_code' => '',
    ];

    public function rules()
    {
        $rules = [
            'clientId' => 'required|exists:clients,id',
            'formId' => 'required|exists:checkout_forms,id',
            'payment_method' => 'required|string',
            'payment_status' => 'required|string',
            'delivery_status' => 'required|string',
            'shipping_address.province' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.address' => 'nullable|string',
            'shipping_address.recipient_name' => 'nullable|string',
            'shipping_address.recipient_mobile' => 'nullable|string',
            'shipping_address.recipient_national_code' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.vendor_product_id' => 'required|exists:market_vendor_products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];

        // Dynamic validation rules based on checkout form fields
        foreach ($this->checkoutFields as $field) {
            if (!empty($field['required'])) {
                $rules['formData.' . $field['id']] = 'required';
            }
        }

        return $rules;
    }

    public function mount(?Order $order = null)
    {
        $this->provinces = ProvinceCity::getProvinces();
        $this->checkoutFormsList = CheckoutForm::where('is_active', true)->get()->toArray();
        
        // Set default form if exists
        if (empty($order) && !empty($this->checkoutFormsList)) {
            $defaultKey = MarketSetting::getValue('checkout.default_form_key');
            $defaultForm = collect($this->checkoutFormsList)->firstWhere('key', $defaultKey) ?: $this->checkoutFormsList[0];
            $this->formId = $defaultForm['id'];
            $this->updatedFormId($this->formId);
        }

        if ($order && $order->exists) {
            $this->order = $order;
            $this->isEdit = true;

            $this->clientId = $order->client_id;
            $client = Client::find($order->client_id);
            if ($client) {
                $this->searchClient = $client->full_name;
            }
            $this->formId = $order->checkout_form_id;
            $this->payment_method = $order->payment_method;
            $this->payment_status = $order->payment_status;
            $this->delivery_status = $order->delivery_status;

            $shipping = $order->shipping_address_json;
            $this->shipping_address = array_merge($this->shipping_address, is_array($shipping) ? $shipping : []);
            if (!empty($this->shipping_address['province'])) {
                $this->cities = ProvinceCity::getCities($this->shipping_address['province']);
            }

            // Load items
            foreach ($order->items as $item) {
                $vp = VendorProduct::find($item->vendor_product_id);
                $this->items[] = [
                    'vendor_product_id' => $item->vendor_product_id,
                    'title' => $item->product_title,
                    'price' => (float)$item->unit_price,
                    'quantity' => (int)$item->quantity,
                    'stock' => $vp ? $vp->stock : 0,
                ];
            }

            // Load metadata
            $this->updatedFormId($this->formId);
            $orderMeta = $order->meta->pluck('value', 'key')->all();
            foreach ($this->checkoutFields as $field) {
                if (isset($orderMeta[$field['id']])) {
                    $this->formData[$field['id']] = $orderMeta[$field['id']];
                }
            }
        }

        $this->searchClients();
        $this->searchProducts();
    }

    public function updatedFormId($formId)
    {
        $form = CheckoutForm::find($formId);
        if ($form) {
            $schema = $form->getSchema();
            $this->checkoutFields = $schema['fields'] ?? [];
            
            // Initialize formData keys
            $defaults = [];
            foreach ($this->checkoutFields as $field) {
                $defaults[$field['id']] = '';
            }
            $this->formData = array_merge($defaults, $this->formData);
        } else {
            $this->checkoutFields = [];
        }
    }

    public function updatedShippingAddressProvince($province)
    {
        $this->cities = ProvinceCity::getCities($province);
        $this->shipping_address['city'] = '';
    }

    public function searchClients()
    {
        $query = Client::query();
        if ($this->searchClient !== '') {
            $query->where(function($q) {
                $q->where('full_name', 'like', '%' . $this->searchClient . '%')
                  ->orWhere('phone', 'like', '%' . $this->searchClient . '%')
                  ->orWhere('email', 'like', '%' . $this->searchClient . '%');
            });
        }
        $this->clientsList = $query->limit(10)->get()->toArray();
    }

    public function searchProducts()
    {
        $user = Auth::user();
        $isAdOrSa = $user->hasRole('super-admin') || $user->hasRole('admin');
        
        $query = VendorProduct::with(['variant.masterProduct']);
        
        if (!$isAdOrSa) {
            $vendor = $user->marketVendor;
            if ($vendor) {
                $query->where('vendor_id', $vendor->id);
            } else {
                $this->productsList = [];
                return;
            }
        }

        if ($this->searchProduct !== '') {
            $query->whereHas('variant.masterProduct', function($q) {
                $q->where('title', 'like', '%' . $this->searchProduct . '%');
            });
        }

        $this->productsList = $query->limit(10)->get()->map(function($vp) {
            $title = optional(optional($vp->variant)->masterProduct)->title ?? 'بدون عنوان';
            $sku = $vp->sku_extension ? " ({$vp->sku_extension})" : "";
            return [
                'id' => $vp->id,
                'title' => $title . $sku,
                'price' => $vp->discount_price > 0 ? $vp->discount_price : $vp->price,
                'stock' => $vp->stock,
            ];
        })->toArray();
    }

    public function addItem($productId)
    {
        $vp = VendorProduct::find($productId);
        if (!$vp) return;

        // Check if already added
        foreach ($this->items as &$item) {
            if ($item['vendor_product_id'] == $productId) {
                if ($item['quantity'] < $vp->stock) {
                    $item['quantity']++;
                } else {
                    $this->dispatch('notify', type: 'warning', text: 'موجودی انبار کافی نیست.');
                }
                return;
            }
        }

        $title = optional(optional($vp->variant)->masterProduct)->title ?? 'بدون عنوان';
        $sku = $vp->sku_extension ? " ({$vp->sku_extension})" : "";

        $this->items[] = [
            'vendor_product_id' => $vp->id,
            'title' => $title . $sku,
            'price' => (float)($vp->discount_price > 0 ? $vp->discount_price : $vp->price),
            'quantity' => 1,
            'stock' => $vp->stock,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function incrementItem($index)
    {
        if ($this->items[$index]['quantity'] < $this->items[$index]['stock']) {
            $this->items[$index]['quantity']++;
        } else {
            $this->dispatch('notify', type: 'warning', text: 'موجودی انبار کافی نیست.');
        }
    }

    public function decrementItem($index)
    {
        if ($this->items[$index]['quantity'] > 1) {
            $this->items[$index]['quantity']--;
        }
    }

    public function getSubtotalProperty(): float
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return $subtotal;
    }

    public function createQuickClient()
    {
        $this->validate([
            'newClient.full_name' => 'required|string|max:255',
            'newClient.phone' => 'required|string|unique:clients,phone',
            'newClient.email' => 'nullable|email|unique:clients,email',
            'newClient.national_code' => 'nullable|string',
        ]);

        $phoneDigits = preg_replace('/\D+/', '', $this->newClient['phone']);
        $baseUsername = $phoneDigits ?: 'clt_' . \Illuminate\Support\Str::random(6);
        $username = $baseUsername;
        $i = 1;
        while (Client::where('username', $username)->exists()) {
            $username = $baseUsername . '_' . $i;
            $i++;
        }

        $client = Client::create([
            'username' => $username,
            'full_name' => $this->newClient['full_name'],
            'phone' => $this->newClient['phone'],
            'email' => $this->newClient['email'],
            'national_code' => $this->newClient['national_code'],
            'created_by' => Auth::id(),
            'status_id' => \Modules\Clients\Entities\ClientStatus::active()->first()?->id,
        ]);

        $this->clientId = $client->id;
        $this->searchClient = $client->full_name;
        $this->searchClients();
        
        $this->newClient = ['full_name' => '', 'phone' => '', 'email' => '', 'national_code' => ''];
        $this->showQuickClientModal = false;
        
        $this->dispatch('notify', type: 'success', text: 'مشتری جدید با موفقیت ثبت شد.');
    }

    public function save(StockService $stockService)
    {
        $this->validate();

        DB::transaction(function () use ($stockService) {
            $user = Auth::user();
            $subtotal = $this->subtotal;

            $orderData = [
                'client_id' => $this->clientId,
                'checkout_form_id' => $this->formId,
                'grand_total' => $subtotal,
                'total_items_price' => $subtotal,
                'total_discount' => 0,
                'payment_method' => $this->payment_method,
                'payment_status' => $this->payment_status,
                'delivery_status' => $this->delivery_status,
                'shipping_address_json' => $this->shipping_address,
            ];

            if ($this->payment_status === 'paid' && empty($this->order?->paid_at)) {
                $orderData['paid_at'] = now();
            }

            if ($this->isEdit) {
                // If editing, first restore old stock before updating
                try {
                    $stockService->releaseReservation($this->order);
                } catch (\Throwable $e) {
                    \Log::error('Failed to release reservation during order edit: ' . $e->getMessage());
                }

                $this->order->update($orderData);
                $order = $this->order;

                // Delete old items
                $order->items()->delete();
            } else {
                $order = Order::create($orderData);
            }

            // Create items and deduct stock
            foreach ($this->items as $itemArray) {
                $vp = VendorProduct::findOrFail($itemArray['vendor_product_id']);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'vendor_product_id' => $vp->id,
                    'vendor_id' => $vp->vendor_id,
                    'product_title' => $itemArray['title'],
                    'quantity' => $itemArray['quantity'],
                    'unit_price' => $itemArray['price'],
                    'total_price' => $itemArray['price'] * $itemArray['quantity'],
                    'unit_tax' => 0,
                    'vendor_commission_rate' => 0,
                ]);

                // Deduct stock for the order
                $stockService->deduct($vp->product_variant_id, $itemArray['quantity'], $vp->id);
            }

            // Save custom metadata
            // Save both custom fields and system fields to OrderMeta to ensure full editability
            $order->meta()->delete();
            foreach ($this->formData as $key => $value) {
                if (!is_null($value) && $value !== '') {
                    OrderMeta::create([
                        'order_id' => $order->id,
                        'key' => $key,
                        'value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value
                    ]);
                }
            }

            // Sync with client profile
            $client = Client::find($this->clientId);
            if ($client) {
                app(\Modules\Market\App\Services\ClientSyncService::class)->sync($order, $client);
            }
        });

        $msg = $this->isEdit ? 'سفارش با موفقیت ویرایش شد.' : 'سفارش با موفقیت ایجاد شد.';
        session()->flash('success', $msg);
        return redirect()->route('user.market.orders.index');
    }

    public function render()
    {
        return view('market::livewire.user.order-form');
    }
}
