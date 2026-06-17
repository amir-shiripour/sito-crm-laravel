<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Market\Entities\ShippingZone;
use Modules\Market\Entities\ShippingMethod;
use Modules\Market\Entities\ShippingRate;
use Modules\Market\Entities\ShippingSlot;
use Modules\Market\Entities\ShippingRule;
use Modules\Market\Entities\Brand;
use Modules\Market\Entities\Category;
use Modules\Market\Entities\DisplayCategory;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\ProductVariant;
use App\Helpers\ProvinceCity;

class ShippingManager extends Component
{
    use WithPagination;

    public $currentTab = 'methods'; // methods, zones, rates, slots, rules

    // Form states
    public $isFormOpen = false;

    // Shipping Method Properties
    public $method_id, $methodName, $methodCode, $methodDriver = 'flat_rate', $methodSettings = [], $methodIsActive = true, $methodSortOrder = 0;

    // Shipping Zone Properties
    public $zone_id, $zoneName, $selectedStates = [], $selectedCities = [], $zoneIsActive = true;
    public $searchProvince = '';
    public $searchCity = '';

    // Shipping Rate Properties
    public $rate_id, $rateMethodId, $rateZoneId, $minWeight = 0, $maxWeight = 9999999, $minOrderPrice = 0, $cost = 0, $perKgCost = 0;

    // Shipping Slot Properties
    public $slot_id, $slotMethodId, $startTime = '09:00', $endTime = '17:00', $capacity = 5;
    public $slotDays = [];
    public $slotStates = [];
    public $slotCities = [];
    public $searchSlotProvince = '';
    public $searchSlotCity = '';
    public $citiesListForSlot = [];

    // Shipping Rule Properties
    public $rule_id, $ruleName, $minGrandTotal = 0, $actionType = 'free_shipping', $actionValue = 0, $ruleIsActive = true;
    public $condBrandIds = [], $condCategoryIds = [], $condProductIds = [], $condDisplayCategoryIds = [], $condVariantIds = [];

    // Search properties for rules tab
    public $searchBrand = '';
    public $searchCategory = '';
    public $searchDisplayCategory = '';
    public $searchProduct = '';
    public $searchVariant = '';

    // Helper data
    public $provincesList = [];
    public $citiesList = [];

    public function mount()
    {
        $this->provincesList = ProvinceCity::getProvinces();
        $this->citiesList = [];
    }

    public function setTab($tab)
    {
        $this->currentTab = $tab;
        $this->closeForm();
        $this->resetPage();
    }

    public function selectAllProvinces()
    {
        $filtered = $this->getFilteredProvinces();
        $this->selectedStates = array_values(array_unique(array_merge($this->selectedStates, $filtered)));
        $this->loadCities();
    }

    public function deselectAllProvinces()
    {
        $filtered = $this->getFilteredProvinces();
        $this->selectedStates = array_values(array_diff($this->selectedStates, $filtered));
        $this->loadCities();
    }

    public function selectAllCities()
    {
        $filtered = $this->getFilteredCities();
        $this->selectedCities = array_values(array_unique(array_merge($this->selectedCities, $filtered)));
    }

    public function deselectAllCities()
    {
        $filtered = $this->getFilteredCities();
        $this->selectedCities = array_values(array_diff($this->selectedCities, $filtered));
    }

    public function toggleState($state)
    {
        if (in_array($state, $this->selectedStates)) {
            $this->selectedStates = array_values(array_diff($this->selectedStates, [$state]));
        } else {
            $this->selectedStates[] = $state;
        }
        $this->loadCities();
    }

    public function toggleCity($city)
    {
        if (in_array($city, $this->selectedCities)) {
            $this->selectedCities = array_values(array_diff($this->selectedCities, [$city]));
        } else {
            $this->selectedCities[] = $city;
        }
    }

    public function getFilteredProvinces()
    {
        if (empty($this->searchProvince)) {
            return $this->provincesList;
        }
        return array_values(array_filter($this->provincesList, function($p) {
            return str_contains(mb_strtolower($p), mb_strtolower($this->searchProvince));
        }));
    }

    public function getFilteredCities()
    {
        if (empty($this->searchCity)) {
            return $this->citiesList;
        }
        return array_values(array_filter($this->citiesList, function($c) {
            return str_contains(mb_strtolower($c), mb_strtolower($this->searchCity));
        }));
    }

    public function updatedSelectedStates($states)
    {
        $this->loadCities();
    }

    public function loadCities()
    {
        $this->citiesList = [];
        $states = $this->selectedStates;

        if (is_string($states)) {
            $states = [$states];
        }

        if (!empty($states) && is_array($states)) {
            foreach ($states as $state) {
                $cities = ProvinceCity::getCities($state);
                if (is_array($cities)) {
                    $this->citiesList = array_merge($this->citiesList, $cities);
                }
            }
            $this->citiesList = array_unique($this->citiesList);
            sort($this->citiesList);
        }

        if (is_array($this->selectedCities)) {
            $this->selectedCities = array_values(array_intersect($this->selectedCities, $this->citiesList));
        } else {
            $this->selectedCities = [];
        }
    }

    public function toggleSlotState($state)
    {
        if (in_array($state, $this->slotStates)) {
            $this->slotStates = array_values(array_diff($this->slotStates, [$state]));
        } else {
            $this->slotStates[] = $state;
        }
        $this->loadCitiesForSlot();
    }

    public function toggleSlotCity($city)
    {
        if (in_array($city, $this->slotCities)) {
            $this->slotCities = array_values(array_diff($this->slotCities, [$city]));
        } else {
            $this->slotCities[] = $city;
        }
    }

    public function toggleSlotDay($day)
    {
        if (in_array($day, $this->slotDays)) {
            $this->slotDays = array_values(array_diff($this->slotDays, [$day]));
        } else {
            $this->slotDays[] = $day;
        }
    }

    public function selectAllSlotProvinces()
    {
        $filtered = $this->getFilteredSlotProvinces();
        $this->slotStates = array_values(array_unique(array_merge($this->slotStates, $filtered)));
        $this->loadCitiesForSlot();
    }

    public function deselectAllSlotProvinces()
    {
        $filtered = $this->getFilteredSlotProvinces();
        $this->slotStates = array_values(array_diff($this->slotStates, $filtered));
        $this->loadCitiesForSlot();
    }

    public function selectAllSlotCities()
    {
        $filtered = $this->getFilteredSlotCities();
        $this->slotCities = array_values(array_unique(array_merge($this->slotCities, $filtered)));
    }

    public function deselectAllSlotCities()
    {
        $filtered = $this->getFilteredSlotCities();
        $this->slotCities = array_values(array_diff($this->slotCities, $filtered));
    }

    public function getFilteredSlotProvinces()
    {
        if (empty($this->searchSlotProvince)) {
            return $this->provincesList;
        }
        return array_values(array_filter($this->provincesList, function($p) {
            return str_contains(mb_strtolower($p), mb_strtolower($this->searchSlotProvince));
        }));
    }

    public function getFilteredSlotCities()
    {
        if (empty($this->searchSlotCity)) {
            return $this->citiesListForSlot;
        }
        return array_values(array_filter($this->citiesListForSlot, function($c) {
            return str_contains(mb_strtolower($c), mb_strtolower($this->searchSlotCity));
        }));
    }

    public function updatedSlotStates($states)
    {
        $this->loadCitiesForSlot();
    }

    public function loadCitiesForSlot()
    {
        $this->citiesListForSlot = [];
        $states = $this->slotStates;

        if (is_string($states)) {
            $states = [$states];
        }

        if (!empty($states) && is_array($states)) {
            foreach ($states as $state) {
                $cities = ProvinceCity::getCities($state);
                if (is_array($cities)) {
                    $this->citiesListForSlot = array_merge($this->citiesListForSlot, $cities);
                }
            }
            $this->citiesListForSlot = array_unique($this->citiesListForSlot);
            sort($this->citiesListForSlot);
        }

        if (is_array($this->slotCities)) {
            $this->slotCities = array_values(array_intersect($this->slotCities, $this->citiesListForSlot));
        } else {
            $this->slotCities = [];
        }
    }

    public function openForm(?int $id = null)
    {
        $this->resetValidation();
        $this->isFormOpen = true;
        $this->reset(['searchProvince', 'searchCity']);

        if ($this->currentTab === 'methods') {
            if ($id) {
                $method = ShippingMethod::findOrFail($id);
                $this->method_id = $method->id;
                $this->methodName = $method->name;
                $this->methodCode = $method->code;
                $this->methodDriver = $method->driver;
                $this->methodSettings = $method->settings ?? [];
                $this->methodIsActive = (bool) $method->is_active;
                $this->methodSortOrder = $method->sort_order;
            } else {
                $this->reset(['method_id', 'methodName', 'methodCode', 'methodSettings', 'methodSortOrder']);
                $this->methodDriver = 'flat_rate';
                $this->methodIsActive = true;
            }
        } elseif ($this->currentTab === 'zones') {
            if ($id) {
                $zone = ShippingZone::findOrFail($id);
                $this->zone_id = $zone->id;
                $this->zoneName = $zone->name;
                $this->selectedStates = $zone->states ?? [];
                $this->selectedCities = $zone->cities ?? [];
                $this->zoneIsActive = (bool) $zone->is_active;
                $this->loadCities();
            } else {
                $this->reset(['zone_id', 'zoneName', 'selectedStates', 'selectedCities']);
                $this->zoneIsActive = true;
                $this->citiesList = [];
            }
        } elseif ($this->currentTab === 'rates') {
            if ($id) {
                $rate = ShippingRate::findOrFail($id);
                $this->rate_id = $rate->id;
                $this->rateMethodId = $rate->shipping_method_id;
                $this->rateZoneId = $rate->shipping_zone_id;
                $this->minWeight = $rate->min_weight;
                $this->maxWeight = $rate->max_weight;
                $this->minOrderPrice = $rate->min_order_price;
                $this->cost = $rate->cost;
                $this->perKgCost = $rate->per_kg_cost;
            } else {
                $this->reset(['rate_id', 'minWeight', 'minOrderPrice', 'cost', 'perKgCost']);
                $this->maxWeight = 9999999;
                $this->rateMethodId = ShippingMethod::first()?->id;
                $this->rateZoneId = ShippingZone::first()?->id;
            }
        } elseif ($this->currentTab === 'slots') {
            $this->reset(['searchSlotProvince', 'searchSlotCity']);
            if ($id) {
                $slot = ShippingSlot::findOrFail($id);
                $this->slot_id = $slot->id;
                $this->slotMethodId = $slot->shipping_method_id;
                $this->slotStates = is_array($slot->states) ? $slot->states : [];
                $this->slotCities = is_array($slot->cities) ? $slot->cities : [];
                $this->slotDays = is_array($slot->days) ? $slot->days : [];
                $this->startTime = date('H:i', strtotime($slot->start_time));
                $this->endTime = date('H:i', strtotime($slot->end_time));
                $this->capacity = $slot->capacity;
                $this->loadCitiesForSlot();
            } else {
                $this->reset(['slot_id', 'slotDays', 'slotStates', 'slotCities']);
                $this->startTime = '09:00';
                $this->endTime = '17:00';
                $this->capacity = 5;
                $this->slotMethodId = ShippingMethod::first()?->id;
                $this->citiesListForSlot = [];
            }
        } elseif ($this->currentTab === 'rules') {
            $this->reset(['searchBrand', 'searchCategory', 'searchDisplayCategory', 'searchProduct', 'searchVariant']);
            if ($id) {
                $rule = ShippingRule::findOrFail($id);
                $this->rule_id = $rule->id;
                $this->ruleName = $rule->name;
                $this->minGrandTotal = $rule->min_grand_total;
                $this->actionType = $rule->action_type;
                $this->actionValue = $rule->action_value;
                $this->ruleIsActive = (bool) $rule->is_active;

                $conds = $rule->conditions ?? [];
                $this->condBrandIds = $conds['brand_ids'] ?? [];
                $this->condCategoryIds = $conds['category_ids'] ?? [];
                $this->condProductIds = $conds['product_ids'] ?? [];
                $this->condDisplayCategoryIds = $conds['display_category_ids'] ?? [];
                $this->condVariantIds = $conds['variant_ids'] ?? [];
            } else {
                $this->reset(['rule_id', 'ruleName', 'minGrandTotal', 'actionValue', 'condBrandIds', 'condCategoryIds', 'condProductIds', 'condDisplayCategoryIds', 'condVariantIds']);
                $this->actionType = 'free_shipping';
                $this->ruleIsActive = true;
            }
        }
    }

    public function closeForm()
    {
        $this->isFormOpen = false;
        $this->resetErrorBag();
    }

    // --- Shipping Methods Operations ---
    public function saveMethod()
    {
        $this->validate([
            'methodName' => 'required|string|max:255',
            'methodCode' => 'required|string|max:255|unique:market_shipping_methods,code,' . $this->method_id,
            'methodDriver' => 'required|string',
            'methodSortOrder' => 'required|integer',
        ], [], [
            'methodName' => 'نام روش حمل و نقل',
            'methodCode' => 'کد روش',
            'methodDriver' => 'درایور محاسبه',
        ]);

        ShippingMethod::updateOrCreate(
            ['id' => $this->method_id],
            [
                'name' => $this->methodName,
                'code' => $this->methodCode,
                'driver' => $this->methodDriver,
                'settings' => $this->methodSettings,
                'is_active' => $this->methodIsActive,
                'sort_order' => $this->methodSortOrder,
            ]
        );

        $this->dispatch('notify', type: 'success', text: 'روش حمل و نقل با موفقیت ذخیره شد.');
        $this->closeForm();
    }

    public function toggleMethodActive($id)
    {
        $method = ShippingMethod::findOrFail($id);
        $method->is_active = !$method->is_active;
        $method->save();
        $this->dispatch('notify', type: 'success', text: 'وضعیت روش حمل و نقل تغییر یافت.');
    }

    public function deleteMethod($id)
    {
        ShippingMethod::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', text: 'روش حمل و نقل حذف شد.');
    }

    // --- Shipping Zones Operations ---
    public function saveZone()
    {
        $this->validate([
            'zoneName' => 'required|string|max:255',
        ], [], [
            'zoneName' => 'نام زون جغرافیایی',
        ]);

        ShippingZone::updateOrCreate(
            ['id' => $this->zone_id],
            [
                'name' => $this->zoneName,
                'states' => $this->selectedStates,
                'cities' => $this->selectedCities,
                'is_active' => $this->zoneIsActive,
            ]
        );

        $this->dispatch('notify', type: 'success', text: 'زون جغرافیایی با موفقیت ذخیره شد.');
        $this->closeForm();
    }

    public function toggleZoneActive($id)
    {
        $zone = ShippingZone::findOrFail($id);
        $zone->is_active = !$zone->is_active;
        $zone->save();
        $this->dispatch('notify', type: 'success', text: 'وضعیت زون تغییر یافت.');
    }

    public function deleteZone($id)
    {
        ShippingZone::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', text: 'زون حذف شد.');
    }

    // --- Shipping Rates Operations ---
    public function saveRate()
    {
        $this->minOrderPrice = $this->parseFormattedPrice($this->minOrderPrice);
        $this->cost = $this->parseFormattedPrice($this->cost);
        $this->perKgCost = $this->parseFormattedPrice($this->perKgCost);

        $this->validate([
            'rateMethodId' => 'required|exists:market_shipping_methods,id',
            'rateZoneId' => 'required|exists:market_shipping_zones,id',
            'minWeight' => 'required|integer|min:0',
            'maxWeight' => 'required|integer|min:0|gte:minWeight',
            'minOrderPrice' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'perKgCost' => 'required|numeric|min:0',
        ], [], [
            'rateMethodId' => 'روش حمل و نقل',
            'rateZoneId' => 'زون جغرافیایی',
            'minWeight' => 'حداقل وزن',
            'maxWeight' => 'حداکثر وزن',
            'minOrderPrice' => 'حداقل مبلغ سفارش',
            'cost' => 'هزینه ارسال',
            'perKgCost' => 'هزینه اضافی هر کیلوگرم',
        ]);

        ShippingRate::updateOrCreate(
            ['id' => $this->rate_id],
            [
                'shipping_method_id' => $this->rateMethodId,
                'shipping_zone_id' => $this->rateZoneId,
                'min_weight' => $this->minWeight,
                'max_weight' => $this->maxWeight,
                'min_order_price' => $this->minOrderPrice,
                'cost' => $this->cost,
                'per_kg_cost' => $this->perKgCost,
            ]
        );

        $this->dispatch('notify', type: 'success', text: 'تعرفه با موفقیت ذخیره شد.');
        $this->closeForm();
    }

    public function deleteRate($id)
    {
        ShippingRate::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', text: 'تعرفه حذف شد.');
    }

    // --- Shipping Slots Operations ---
    public function saveSlot()
    {
        $this->validate([
            'slotMethodId' => 'required|exists:market_shipping_methods,id',
            'slotDays' => 'required|array|min:1',
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i|after:startTime',
            'capacity' => 'required|integer|min:1',
        ], [], [
            'slotMethodId' => 'روش حمل و نقل',
            'slotDays' => 'روزهای هفته',
            'startTime' => 'ساعت شروع',
            'endTime' => 'ساعت پایان',
            'capacity' => 'ظرفیت کل',
        ]);

        $states = empty($this->slotStates) ? null : $this->slotStates;
        $cities = empty($this->slotCities) ? null : $this->slotCities;

        ShippingSlot::updateOrCreate(
            ['id' => $this->slot_id],
            [
                'shipping_method_id' => $this->slotMethodId,
                'days' => $this->slotDays,
                'states' => $states,
                'cities' => $cities,
                'start_time' => $this->startTime,
                'end_time' => $this->endTime,
                'capacity' => $this->capacity,
            ]
        );

        $this->dispatch('notify', type: 'success', text: 'بازه زمانی تحویل با موفقیت ذخیره شد.');
        $this->closeForm();
    }

    public function deleteSlot($id)
    {
        ShippingSlot::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', text: 'بازه زمانی حذف شد.');
    }

    // --- Shipping Rules Operations ---
    public function saveRule()
    {
        $this->minGrandTotal = $this->parseFormattedPrice($this->minGrandTotal);
        if ($this->actionType !== 'percentage_discount') {
            $this->actionValue = $this->parseFormattedPrice($this->actionValue);
        }

        $this->validate([
            'ruleName' => 'required|string|max:255',
            'minGrandTotal' => 'nullable|numeric|min:0',
            'actionType' => 'required|string',
            'actionValue' => 'required|numeric|min:0',
        ], [], [
            'ruleName' => 'نام قانون',
            'actionType' => 'نوع تخفیف ارسال',
            'actionValue' => 'مقدار تخفیف/ارزش',
        ]);

        $conditions = [
            'brand_ids' => array_map('intval', $this->condBrandIds),
            'category_ids' => array_map('intval', $this->condCategoryIds),
            'display_category_ids' => array_map('intval', $this->condDisplayCategoryIds),
            'product_ids' => array_map('intval', $this->condProductIds),
            'variant_ids' => array_map('intval', $this->condVariantIds),
        ];

        ShippingRule::updateOrCreate(
            ['id' => $this->rule_id],
            [
                'name' => $this->ruleName,
                'conditions' => $conditions,
                'min_grand_total' => $this->minGrandTotal ?: 0,
                'action_type' => $this->actionType,
                'action_value' => $this->actionValue,
                'is_active' => $this->ruleIsActive,
            ]
        );

        $this->dispatch('notify', type: 'success', text: 'قانون تخفیف ارسال با موفقیت ذخیره شد.');
        $this->closeForm();
    }

    public function toggleRuleActive($id)
    {
        $rule = ShippingRule::findOrFail($id);
        $rule->is_active = !$rule->is_active;
        $rule->save();
        $this->dispatch('notify', type: 'success', text: 'وضعیت قانون تغییر یافت.');
    }

    public function deleteRule($id)
    {
        ShippingRule::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', text: 'قانون تخفیف ارسال حذف شد.');
    }

    // Advanced Selectors Toggle Helpers
    public function toggleRuleBrand($id)
    {
        $id = (int) $id;
        if (in_array($id, $this->condBrandIds)) {
            $this->condBrandIds = array_values(array_diff($this->condBrandIds, [$id]));
        } else {
            $this->condBrandIds[] = $id;
        }
    }

    public function toggleRuleCategory($id)
    {
        $id = (int) $id;
        if (in_array($id, $this->condCategoryIds)) {
            $this->condCategoryIds = array_values(array_diff($this->condCategoryIds, [$id]));
        } else {
            $this->condCategoryIds[] = $id;
        }
    }

    public function toggleRuleDisplayCategory($id)
    {
        $id = (int) $id;
        if (in_array($id, $this->condDisplayCategoryIds)) {
            $this->condDisplayCategoryIds = array_values(array_diff($this->condDisplayCategoryIds, [$id]));
        } else {
            $this->condDisplayCategoryIds[] = $id;
        }
    }

    public function toggleRuleProduct($id)
    {
        $id = (int) $id;
        if (in_array($id, $this->condProductIds)) {
            $this->condProductIds = array_values(array_diff($this->condProductIds, [$id]));
        } else {
            $this->condProductIds[] = $id;
        }
    }

    public function toggleRuleVariant($id)
    {
        $id = (int) $id;
        if (in_array($id, $this->condVariantIds)) {
            $this->condVariantIds = array_values(array_diff($this->condVariantIds, [$id]));
        } else {
            $this->condVariantIds[] = $id;
        }
    }

    // Select/Deselect All Filtered Helpers
    public function selectAllFilteredBrands()
    {
        $filtered = Brand::where('is_active', true)
            ->when($this->searchBrand, fn($q) => $q->where('name', 'like', '%' . $this->searchBrand . '%'))
            ->pluck('id')
            ->toArray();
        $this->condBrandIds = array_values(array_unique(array_merge($this->condBrandIds, $filtered)));
    }

    public function deselectAllFilteredBrands()
    {
        $filtered = Brand::where('is_active', true)
            ->when($this->searchBrand, fn($q) => $q->where('name', 'like', '%' . $this->searchBrand . '%'))
            ->pluck('id')
            ->toArray();
        $this->condBrandIds = array_values(array_diff($this->condBrandIds, $filtered));
    }

    public function selectAllFilteredCategories()
    {
        $filtered = Category::when($this->searchCategory, fn($q) => $q->where('name', 'like', '%' . $this->searchCategory . '%'))
            ->pluck('id')
            ->toArray();
        $this->condCategoryIds = array_values(array_unique(array_merge($this->condCategoryIds, $filtered)));
    }

    public function deselectAllFilteredCategories()
    {
        $filtered = Category::when($this->searchCategory, fn($q) => $q->where('name', 'like', '%' . $this->searchCategory . '%'))
            ->pluck('id')
            ->toArray();
        $this->condCategoryIds = array_values(array_diff($this->condCategoryIds, $filtered));
    }

    public function selectAllFilteredDisplayCategories()
    {
        $filtered = DisplayCategory::when($this->searchDisplayCategory, fn($q) => $q->where('name', 'like', '%' . $this->searchDisplayCategory . '%'))
            ->pluck('id')
            ->toArray();
        $this->condDisplayCategoryIds = array_values(array_unique(array_merge($this->condDisplayCategoryIds, $filtered)));
    }

    public function deselectAllFilteredDisplayCategories()
    {
        $filtered = DisplayCategory::when($this->searchDisplayCategory, fn($q) => $q->where('name', 'like', '%' . $this->searchDisplayCategory . '%'))
            ->pluck('id')
            ->toArray();
        $this->condDisplayCategoryIds = array_values(array_diff($this->condDisplayCategoryIds, $filtered));
    }

    public function selectAllFilteredProducts()
    {
        $filtered = MasterProduct::where('status', 'active')
            ->when($this->searchProduct, fn($q) => $q->where('title', 'like', '%' . $this->searchProduct . '%'))
            ->pluck('id')
            ->toArray();
        $this->condProductIds = array_values(array_unique(array_merge($this->condProductIds, $filtered)));
    }

    public function deselectAllFilteredProducts()
    {
        $filtered = MasterProduct::where('status', 'active')
            ->when($this->searchProduct, fn($q) => $q->where('title', 'like', '%' . $this->searchProduct . '%'))
            ->pluck('id')
            ->toArray();
        $this->condProductIds = array_values(array_diff($this->condProductIds, $filtered));
    }

    public function selectAllFilteredVariants()
    {
        $filtered = ProductVariant::where('is_active', true)
            ->where('variant_attributes', 'not like', '%"استاندارد"%')
            ->when($this->searchVariant, function($q) {
                $q->where(function($sub) {
                    $sub->whereHas('masterProduct', function($pQuery) {
                        $pQuery->where('title', 'like', '%' . $this->searchVariant . '%');
                    })
                    ->orWhere('variant_code', 'like', '%' . $this->searchVariant . '%')
                    ->orWhere('variant_attributes', 'like', '%' . $this->searchVariant . '%');
                });
            })
            ->pluck('id')
            ->toArray();
        $this->condVariantIds = array_values(array_unique(array_merge($this->condVariantIds, $filtered)));
    }

    public function deselectAllFilteredVariants()
    {
        $filtered = ProductVariant::where('is_active', true)
            ->where('variant_attributes', 'not like', '%"استاندارد"%')
            ->when($this->searchVariant, function($q) {
                $q->where(function($sub) {
                    $sub->whereHas('masterProduct', function($pQuery) {
                        $pQuery->where('title', 'like', '%' . $this->searchVariant . '%');
                    })
                    ->orWhere('variant_code', 'like', '%' . $this->searchVariant . '%')
                    ->orWhere('variant_attributes', 'like', '%' . $this->searchVariant . '%');
                });
            })
            ->pluck('id')
            ->toArray();
        $this->condVariantIds = array_values(array_diff($this->condVariantIds, $filtered));
    }

    // Helper to fetch searchable items ensuring already selected ones are not hidden
    private function getSearchableItems($modelClass, $searchField, $searchQuery, $selectedIds, $extraConditions = null)
    {
        // 1. Fetch selected items
        $selectedItems = collect();
        if (!empty($selectedIds)) {
            $selectedQuery = $modelClass::whereIn('id', $selectedIds);
            if ($extraConditions) {
                $selectedQuery = $extraConditions($selectedQuery);
            }
            $selectedItems = $selectedQuery->get();
        }

        // 2. Fetch search/general items
        $generalQuery = $modelClass::query();
        if ($extraConditions) {
            $generalQuery = $extraConditions($generalQuery);
        }
        if (!empty($searchQuery)) {
            $generalQuery->where($searchField, 'like', '%' . $searchQuery . '%');
            $generalItems = $generalQuery->take(50)->get();
        } else {
            $generalItems = $generalQuery->take(30)->get();
        }

        // 3. Merge and return unique items
        return $selectedItems->merge($generalItems)->unique('id')->values();
    }

    // Helper to fetch searchable variations ensuring already selected ones are not hidden
    private function getSearchableVariants($searchQuery, $selectedVariantIds)
    {
        // 1. Fetch selected variants
        $selectedVariants = collect();
        if (!empty($selectedVariantIds)) {
            $selectedVariants = ProductVariant::with('masterProduct')
                ->whereIn('id', $selectedVariantIds)
                ->get();
        }

        // 2. Fetch search/general variants
        $generalQuery = ProductVariant::with('masterProduct')
            ->where('is_active', true)
            ->where('variant_attributes', 'not like', '%"استاندارد"%');

        if (!empty($searchQuery)) {
            $generalQuery->where(function($q) use ($searchQuery) {
                $q->whereHas('masterProduct', function($sub) use ($searchQuery) {
                    $sub->where('title', 'like', '%' . $searchQuery . '%');
                })
                ->orWhere('variant_code', 'like', '%' . $searchQuery . '%')
                ->orWhere('variant_attributes', 'like', '%' . $searchQuery . '%');
            });
            $generalVariants = $generalQuery->take(50)->get();
        } else {
            $generalVariants = $generalQuery->take(30)->get();
        }

        return $selectedVariants->merge($generalVariants)->unique('id')->values();
    }

    public function getCurrencyLabel(): string
    {
        $currency = \Modules\Market\Entities\MarketSetting::getValue('general.currency', 'toman');
        return $currency === 'rial' ? 'ریال' : 'تومان';
    }

    private function parseFormattedPrice($value)
    {
        if (empty($value)) {
            return 0;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        return (float) str_replace(',', '', $value);
    }

    public function render()
    {
        $brandsList = $this->getSearchableItems(
            Brand::class,
            'name',
            $this->searchBrand,
            $this->condBrandIds,
            fn($q) => $q->where('is_active', true)
        );

        $categoriesList = $this->getSearchableItems(
            Category::class,
            'name',
            $this->searchCategory,
            $this->condCategoryIds
        );

        $displayCategoriesList = $this->getSearchableItems(
            DisplayCategory::class,
            'name',
            $this->searchDisplayCategory,
            $this->condDisplayCategoryIds
        );

        $productsList = $this->getSearchableItems(
            MasterProduct::class,
            'title',
            $this->searchProduct,
            $this->condProductIds,
            fn($q) => $q->where('status', 'active')
        );

        $variantsList = $this->getSearchableVariants(
            $this->searchVariant,
            $this->condVariantIds
        );

        return view('market::livewire.admin.shipping-manager', [
            'methods' => ShippingMethod::orderBy('sort_order', 'asc')->paginate(10, ['*'], 'methodsPage'),
            'zones' => ShippingZone::latest()->paginate(10, ['*'], 'zonesPage'),
            'rates' => ShippingRate::with(['method', 'zone'])->latest()->paginate(15, ['*'], 'ratesPage'),
            'slots' => ShippingSlot::with(['method'])->latest()->paginate(15, ['*'], 'slotsPage'),
            'rules' => ShippingRule::latest()->paginate(10, ['*'], 'rulesPage'),
            'brands' => $brandsList,
            'categories' => $categoriesList,
            'displayCategories' => $displayCategoriesList,
            'products' => $productsList,
            'variants' => $variantsList,
        ]);
    }
}
