<?php

namespace Modules\FieldService\App\Livewire\Admin;

use App\Helpers\ProvinceCity;
use Livewire\Component;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Models\Category;
use Modules\FieldService\App\Models\FieldServiceSetting;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Services\MapProviderService;

class FleetLiveMap extends Component
{
    // Filter controls
    public string $selectedProvince = 'all';
    public string $selectedCity = 'all';
    public string $selectedCategory = 'all';
    public string $techStatusFilter = 'all'; // all, online_free, busy, offline
    public string $orderPriorityFilter = 'all'; // all, emergency, searching, in_progress
    public string $search = '';

    // Interactive UI controls
    public string $activeSideTab = 'techs'; // techs | orders
    public bool $showSidebar = true;
    public ?int $focusedEntityId = null;
    public ?string $focusedEntityType = null;

    public function updatedSelectedProvince($value): void
    {
        $this->selectedCity = 'all';
    }

    public function toggleSidebar(): void
    {
        $this->showSidebar = !$this->showSidebar;
    }

    public function focusEntity(string $type, int $id, float $lat, float $lng, string $title): void
    {
        $this->focusedEntityId = $id;
        $this->focusedEntityType = $type;

        $this->dispatch('focus-map-entity', [
            'type'  => $type,
            'id'    => $id,
            'lat'   => $lat,
            'lng'   => $lng,
            'title' => $title,
            'zoom'  => 16,
        ]);
    }

    public function resetFilters(): void
    {
        $this->selectedProvince = 'all';
        $this->selectedCity = 'all';
        $this->selectedCategory = 'all';
        $this->techStatusFilter = 'all';
        $this->orderPriorityFilter = 'all';
        $this->search = '';
    }

    public function render()
    {
        $mapProvider = app(MapProviderService::class);
        $mapConfig = $mapProvider->getMapConfig();

        // 1. Coverage metadata from settings
        $coveredProvinces = FieldServiceSetting::getCoveredProvinces();
        $coveredCities = FieldServiceSetting::getCoveredCities();

        // Available provinces for filter (covered provinces first, fallback to all provinces)
        $availableProvinces = !empty($coveredProvinces) ? $coveredProvinces : ProvinceCity::getProvinces();

        // Available cities for filter
        if ($this->selectedProvince !== 'all') {
            $availableCities = ProvinceCity::getCities($this->selectedProvince);
        } else {
            $availableCities = !empty($coveredCities) ? $coveredCities : ProvinceCity::getCities($coveredProvinces[0] ?? 'مازندران');
        }

        // Available service categories
        $allCategories = Category::where('is_active', true)->orderBy('sort_order')->get();

        // 2. Query Technicians with filters
        $techQuery = Technician::query()
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->with(['categories', 'orders']);

        if ($this->selectedProvince !== 'all') {
            $techQuery->where('province', $this->selectedProvince);
        }

        if ($this->selectedCity !== 'all') {
            $techQuery->where('city', $this->selectedCity);
        }

        if ($this->selectedCategory !== 'all') {
            $catId = (int) $this->selectedCategory;
            $techQuery->whereHas('categories', function ($q) use ($catId) {
                $q->where('field_service_categories.id', $catId);
            });
        }

        if ($this->techStatusFilter === 'online_free') {
            $techQuery->where('is_online', true)->where('is_busy', false);
        } elseif ($this->techStatusFilter === 'busy') {
            $techQuery->where('is_busy', true);
        } elseif ($this->techStatusFilter === 'offline') {
            $techQuery->where('is_online', false);
        }

        if (!empty($this->search)) {
            $s = trim($this->search);
            $techQuery->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('mobile', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%");
            });
        }

        $technicians = $techQuery->get();

        // 3. Query Active Orders with filters
        $orderQuery = ServiceOrder::query()
            ->whereNotIn('status', [OrderStatus::COMPLETED, OrderStatus::CANCELLED])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->with(['client', 'technician', 'category']);

        if ($this->selectedProvince !== 'all') {
            $orderQuery->where('province', $this->selectedProvince);
        }

        if ($this->selectedCity !== 'all') {
            $orderQuery->where('city', $this->selectedCity);
        }

        if ($this->selectedCategory !== 'all') {
            $orderQuery->where('category_id', (int) $this->selectedCategory);
        }

        if ($this->orderPriorityFilter === 'emergency') {
            $orderQuery->where('is_emergency', true);
        } elseif ($this->orderPriorityFilter === 'searching') {
            $orderQuery->where('status', OrderStatus::SEARCHING);
        } elseif ($this->orderPriorityFilter === 'in_progress') {
            $orderQuery->whereIn('status', [OrderStatus::EN_ROUTE, OrderStatus::ARRIVED, OrderStatus::IN_PROGRESS]);
        }

        if (!empty($this->search)) {
            $s = trim($this->search);
            $orderQuery->where(function ($q) use ($s) {
                $q->where('order_number', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%")
                  ->orWhere('customer_phone', 'like', "%{$s}%")
                  ->orWhere('address', 'like', "%{$s}%");
            });
        }

        $activeOrders = $orderQuery->get();

        // 4. Global KPIs
        $stats = [
            'online_free'      => Technician::where('is_online', true)->where('is_busy', false)->count(),
            'busy_count'       => Technician::where('is_busy', true)->count(),
            'offline_count'    => Technician::where('is_online', false)->count(),
            'total_techs'      => Technician::count(),
            'orders_count'     => ServiceOrder::whereNotIn('status', [OrderStatus::COMPLETED, OrderStatus::CANCELLED])->count(),
            'emergency_count'  => ServiceOrder::whereNotIn('status', [OrderStatus::COMPLETED, OrderStatus::CANCELLED])->where('is_emergency', true)->count(),
            'searching_count'  => ServiceOrder::where('status', OrderStatus::SEARCHING)->count(),
        ];

        // 5. Serialize data for Leaflet JS Alpine layer
        $serializedTechs = $technicians->map(function ($t) {
            return [
                'id'            => $t->id,
                'first_name'    => $t->first_name,
                'last_name'     => $t->last_name,
                'full_name'     => $t->full_name,
                'mobile'        => $t->mobile,
                'city'          => $t->city,
                'province'      => $t->province,
                'lat'           => (float) $t->lat,
                'lng'           => (float) $t->lng,
                'is_online'     => (bool) $t->is_online,
                'is_busy'       => (bool) $t->is_busy,
                'status'        => $t->status?->value ?? 'active',
                'tier'          => $t->tier?->label() ?? 'نقره‌ای',
                'rating_avg'    => (float) $t->rating_avg,
                'categories'    => $t->categories->pluck('name')->toArray(),
            ];
        })->values()->toArray();

        $serializedOrders = $activeOrders->map(function ($o) {
            return [
                'id'                => $o->id,
                'order_number'      => $o->order_number,
                'customer_name'     => $o->customer_name,
                'customer_phone'    => $o->customer_phone,
                'city'              => $o->city,
                'province'          => $o->province,
                'address'           => $o->address,
                'lat'               => (float) $o->lat,
                'lng'               => (float) $o->lng,
                'is_emergency'      => (bool) $o->is_emergency,
                'status_label'      => $o->status?->label() ?? 'در جریان',
                'status_code'       => $o->status?->value ?? 'searching',
                'category_name'     => $o->category?->name ?? 'خدمت عمومی',
                'category_icon'     => $o->category?->icon ?? '🛠️',
                'search_radius_km'  => (float) ($o->search_radius_km ?? 5),
                'technician_name'   => $o->technician?->full_name ?? null,
            ];
        })->values()->toArray();

        // Dispatch reactive update to Leaflet map
        $this->dispatch('fleet-markers-updated', [
            'technicians' => $serializedTechs,
            'orders'      => $serializedOrders,
        ]);

        return view('fieldservice::livewire.admin.fleet-live-map', [
            'mapConfig'           => $mapConfig,
            'technicians'         => $technicians,
            'activeOrders'        => $activeOrders,
            'serializedTechs'     => $serializedTechs,
            'serializedOrders'    => $serializedOrders,
            'stats'               => $stats,
            'availableProvinces'  => $availableProvinces,
            'availableCities'     => $availableCities,
            'allCategories'       => $allCategories,
            'selectedProvince'    => $this->selectedProvince,
            'selectedCity'        => $this->selectedCity,
            'selectedCategory'    => $this->selectedCategory,
            'techStatusFilter'    => $this->techStatusFilter,
            'orderPriorityFilter' => $this->orderPriorityFilter,
            'search'              => $this->search,
            'showSidebar'         => $this->showSidebar,
            'activeSideTab'       => $this->activeSideTab,
            'focusedEntityId'     => $this->focusedEntityId,
            'focusedEntityType'   => $this->focusedEntityType,
        ]);
    }
}
