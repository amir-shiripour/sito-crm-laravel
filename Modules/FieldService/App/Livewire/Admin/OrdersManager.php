<?php

namespace Modules\FieldService\App\Livewire\Admin;

use App\Helpers\ProvinceCity;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Clients\Entities\Client;
use Modules\FieldService\App\Enums\ApplianceType;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Models\Building;
use Modules\FieldService\App\Models\Category;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Models\VendorPartner;
use Modules\FieldService\App\Services\DispatchRadarService;
use Modules\FieldService\App\Services\FieldServiceWorkflowBridgeService;

class OrdersManager extends Component
{
    use WithPagination;

    // Filters and Search
    public string $search = '';
    public string $statusFilter = 'all';
    public string $priorityFilter = 'all';

    // Create Order Modal State
    public bool $showCreateModal = false;

    // Client Selection / Quick Create
    public ?int $selectedClientId = null;
    public string $clientSearch = '';
    public bool $isCreatingNewClient = false;
    public string $newClientName = '';
    public string $newClientPhone = '';

    // Order Form Fields
    public ?int $category_id = null;
    public string $appliance_type = 'package';
    public string $appliance_brand_model = '';
    public string $problem_description = '';
    public string $priority = 'normal';
    public string $province = 'مازندران';
    public string $city = 'بابل';
    public string $district = '';
    public string $address = '';
    public ?int $building_id = null;
    public ?int $vendor_id = null;

    // Dispatch Mode: 'radar' (auto dispatch via radar) or 'manual' (assign directly to tech)
    public string $dispatchMode = 'radar';
    public ?int $manualTechnicianId = null;

    // Order Details Drawer State
    public bool $showDetailDrawer = false;
    public ?int $viewingOrderId = null;

    protected $queryString = ['search', 'statusFilter', 'priorityFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter()
    {
        $this->resetPage();
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

    public function updatedCategoryId($value)
    {
        if ($value) {
            $cat = Category::find($value);
            if ($cat && $cat->appliance_type) {
                $this->appliance_type = $cat->appliance_type->value;
            }
        }
    }

    public function selectClient(int $clientId)
    {
        $client = Client::find($clientId);
        if ($client) {
            $this->selectedClientId = $client->id;
            $this->clientSearch = $client->full_name . ' (' . ($client->phone ?? 'بدون شماره') . ')';

            // Pre-fill address if available
            $defaultAddr = $client->addresses()->where('is_default', true)->first() ?: $client->addresses()->first();
            if ($defaultAddr) {
                if (!empty($defaultAddr->province)) {
                    $this->province = $defaultAddr->province;
                }
                if (!empty($defaultAddr->city)) {
                    $this->city = $defaultAddr->city;
                }
                if (!empty($defaultAddr->address)) {
                    $this->address = $defaultAddr->address;
                }
            }
        }
    }

    public function clearClientSelection()
    {
        $this->selectedClientId = null;
        $this->clientSearch = '';
    }

    public function openCreateModal()
    {
        $this->reset([
            'selectedClientId',
            'clientSearch',
            'isCreatingNewClient',
            'newClientName',
            'newClientPhone',
            'appliance_brand_model',
            'problem_description',
            'district',
            'address',
            'building_id',
            'vendor_id',
            'manualTechnicianId',
        ]);

        $defaultCat = Category::where('is_active', true)->orderBy('sort_order')->first();
        $this->category_id = $defaultCat?->id;
        $this->appliance_type = $defaultCat?->appliance_type?->value ?? 'package';
        $this->priority = 'normal';
        $this->province = 'مازندران';
        $this->city = 'بابل';
        $this->dispatchMode = 'radar';

        $this->showCreateModal = true;
    }

    public function saveNewOrder()
    {
        $this->validate([
            'category_id'         => 'required|exists:field_service_categories,id',
            'problem_description' => 'required|string|min:4',
            'province'            => 'required|string',
            'city'                => 'required|string',
            'address'             => 'required|string|min:4',
            'priority'            => 'required|in:normal,emergency',
        ]);

        // 1. Resolve CRM Client
        if ($this->isCreatingNewClient) {
            $this->validate([
                'newClientName'  => 'required|string|min:3',
                'newClientPhone' => 'required|string|min:10',
            ]);

            $client = Client::where('phone', $this->newClientPhone)->first();
            if (!$client) {
                $username = $this->newClientPhone;
                if (Client::where('username', $username)->exists()) {
                    $username .= '_' . \Illuminate\Support\Str::random(4);
                }
                $client = Client::create([
                    'username'   => $username,
                    'full_name'  => $this->newClientName,
                    'phone'      => $this->newClientPhone,
                    'created_by' => auth()->id(),
                ]);
            }
            $this->selectedClientId = $client->id;
        } elseif (!$this->selectedClientId) {
            $this->addError('clientSearch', 'لطفاً یک مشتری را جستجو و انتخاب کنید یا مشتری جدید ثبت نمایید.');
            return;
        } else {
            $client = Client::findOrFail($this->selectedClientId);
        }

        // 2. Determine initial status
        $initialStatus = ($this->dispatchMode === 'manual' && $this->manualTechnicianId)
            ? OrderStatus::ACCEPTED
            : OrderStatus::SEARCHING;

        // 3. Create Service Order
        $order = ServiceOrder::create([
            'order_number'          => ServiceOrder::generateOrderNumber(),
            'client_id'             => $client->id,
            'technician_id'         => ($this->dispatchMode === 'manual') ? $this->manualTechnicianId : null,
            'category_id'           => $this->category_id,
            'building_id'           => $this->building_id ?: null,
            'vendor_id'             => $this->vendor_id ?: null,
            'appliance_type'        => ApplianceType::tryFrom($this->appliance_type) ?? ApplianceType::PACKAGE,
            'appliance_brand_model' => $this->appliance_brand_model,
            'problem_description'   => $this->problem_description,
            'status'                => $initialStatus,
            'priority'              => $this->priority,
            'is_emergency'          => $this->priority === 'emergency',
            'province'              => $this->province,
            'city'                  => $this->city,
            'district'              => $this->district,
            'address'               => $this->address,
            'customer_name'         => $client->full_name,
            'customer_phone'        => $client->phone,
            'search_radius_km'      => 3,
            'dispatched_at'         => now(),
            'accepted_at'           => ($this->dispatchMode === 'manual') ? now() : null,
        ]);

        // 4. Handle manual assignment or radar dispatch
        if ($this->dispatchMode === 'manual' && $this->manualTechnicianId) {
            $tech = Technician::find($this->manualTechnicianId);
            if ($tech) {
                $tech->is_busy = true;
                $tech->save();
            }
        } else {
            // Activate radar
            app(DispatchRadarService::class)->dispatchOrder($order);
        }

        // 5. Trigger Workflow Event
        app(FieldServiceWorkflowBridgeService::class)->triggerOrderCreated($order);

        $this->showCreateModal = false;
        $this->dispatch('notify', [
            'type' => 'success',
            'text' => "سفارش تلفنی #{$order->order_number} برای مشتری «{$client->full_name}» با موفقیت ثبت و اعزام شد.",
        ]);
    }

    public function openOrderDetail(int $orderId)
    {
        $this->viewingOrderId = $orderId;
        $this->showDetailDrawer = true;
    }

    public function closeDetailDrawer()
    {
        $this->showDetailDrawer = false;
        $this->viewingOrderId = null;
    }

    public function reDispatch(int $orderId)
    {
        $order = ServiceOrder::find($orderId);
        if ($order) {
            $radar = app(DispatchRadarService::class);
            $res = $radar->dispatchOrder($order);

            $count = $res['technicians_count'] ?? 0;
            $radius = $res['radius_km'] ?? $order->search_radius_km;

            $this->dispatch('notify', [
                'type' => 'success',
                'text' => "سفارش #{$order->order_number} مجدداً در شعاع {$radius} کیلومتری برای {$count} تکنسین ارسال شد.",
            ]);
        }
    }

    public function assignTechnician(int $orderId, int $technicianId)
    {
        $order = ServiceOrder::find($orderId);
        $tech = Technician::find($technicianId);

        if ($order && $tech) {
            $radar = app(DispatchRadarService::class);
            $radar->acceptOrder($order, $tech);

            $this->dispatch('notify', [
                'type' => 'success',
                'text' => "سفارش #{$order->order_number} به تکنسین {$tech->full_name} تخصیص یافت.",
            ]);
        }
    }

    public function updateOrderStatus(int $orderId, string $statusValue)
    {
        $order = ServiceOrder::findOrFail($orderId);
        $newStatus = OrderStatus::tryFrom($statusValue);
        if ($newStatus) {
            $order->status = $newStatus;
            if ($newStatus === OrderStatus::COMPLETED) {
                $order->completed_at = now();
                if ($order->technician) {
                    $order->technician->is_busy = false;
                    $order->technician->increment('completed_orders_count');
                    $order->technician->save();
                }
            }
            $order->save();

            $this->dispatch('notify', [
                'type' => 'info',
                'text' => "وضعیت سفارش به «{$newStatus->label()}» تغییر یافت.",
            ]);
        }
    }

    public function cancelOrder(int $orderId)
    {
        $order = ServiceOrder::find($orderId);
        if ($order) {
            $order->status = OrderStatus::CANCELLED;
            $order->cancelled_at = now();
            $order->cancellation_reason = 'لغو دستی توسط اپراتور سیستم';
            $order->save();

            if ($order->technician) {
                $order->technician->is_busy = false;
                $order->technician->save();
            }

            $this->dispatch('notify', [
                'type' => 'warning',
                'text' => "سفارش #{$order->order_number} لغو گردید.",
            ]);
        }
    }

    public function render()
    {
        $query = ServiceOrder::query()
            ->with(['client', 'technician', 'category', 'invoice', 'building', 'vendor'])
            ->latest();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('order_number', 'like', "%{$this->search}%")
                  ->orWhere('customer_name', 'like', "%{$this->search}%")
                  ->orWhere('customer_phone', 'like', "%{$this->search}%")
                  ->orWhere('city', 'like', "%{$this->search}%")
                  ->orWhere('appliance_brand_model', 'like', "%{$this->search}%");
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->priorityFilter !== 'all') {
            $query->where('priority', $this->priorityFilter);
        }

        $orders = $query->paginate(15);

        // Stats Summary
        $totalOrdersCount = ServiceOrder::count();
        $searchingCount = ServiceOrder::whereIn('status', [OrderStatus::SEARCHING, OrderStatus::DISPATCHED])->count();
        $inProgressCount = ServiceOrder::whereIn('status', [
            OrderStatus::ACCEPTED,
            OrderStatus::EN_ROUTE,
            OrderStatus::ARRIVED,
            OrderStatus::IN_PROGRESS,
            OrderStatus::INVOICE_PENDING,
        ])->count();
        $completedCount = ServiceOrder::where('status', OrderStatus::COMPLETED)->count();

        // Client search suggestions for modal
        $clientSuggestions = collect();
        if (strlen($this->clientSearch) >= 2 && !$this->selectedClientId) {
            $clientSuggestions = Client::query()
                ->where('full_name', 'like', "%{$this->clientSearch}%")
                ->orWhere('phone', 'like', "%{$this->clientSearch}%")
                ->limit(6)
                ->get();
        }

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $onlineTechs = Technician::where('is_online', true)->get();
        $allTechs = Technician::orderBy('first_name')->get();
        $buildings = Building::orderBy('name')->get();
        $vendors = VendorPartner::where('status', 'active')->orderBy('store_name')->get();
        $provinces = ProvinceCity::getProvinces();
        $cities = ProvinceCity::getCities($this->province);

        // Currently viewed order for drawer
        $viewingOrder = $this->viewingOrderId ? ServiceOrder::with(['client', 'technician', 'category', 'invoice.items', 'building', 'vendor'])->find($this->viewingOrderId) : null;

        return view('fieldservice::livewire.admin.orders-manager', [
            'orders'             => $orders,
            'totalOrdersCount'   => $totalOrdersCount,
            'searchingCount'     => $searchingCount,
            'inProgressCount'    => $inProgressCount,
            'completedCount'     => $completedCount,
            'clientSuggestions'  => $clientSuggestions,
            'categories'         => $categories,
            'onlineTechs'        => $onlineTechs,
            'allTechs'           => $allTechs,
            'buildings'          => $buildings,
            'vendors'            => $vendors,
            'provinces'          => $provinces,
            'cities'             => $cities,
            'viewingOrder'       => $viewingOrder,
        ]);
    }
}
