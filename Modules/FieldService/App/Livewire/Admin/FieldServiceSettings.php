<?php

namespace Modules\FieldService\App\Livewire\Admin;

use App\Helpers\ProvinceCity;
use Livewire\Component;
use Modules\FieldService\App\Models\FieldServiceSetting;

class FieldServiceSettings extends Component
{
    // Map Settings
    public string $map_provider = 'osm';
    public string $map_neshan_api_key = '';
    public string $map_map_ir_api_key = '';
    public string $map_custom_tile_url = '';
    public string $map_custom_api_key = '';
    public float $map_default_lat = 36.5444;
    public float $map_default_lng = 52.6780;
    public int $map_default_zoom = 13;

    // Coverage Management (Restricted strictly to Iran and active regions)
    public array $covered_provinces = [];
    public array $covered_cities = [];
    public string $active_province_tab = 'مازندران';
    public string $city_search = '';

    // Financial & Operations
    public float $default_commission_percent = 15.0;
    public float $base_travel_fee = 60000;
    public int $dispatch_timeout_seconds = 45;
    public int $default_warranty_days = 90;

    // Frontend & Landing Settings
    public string $landing_announcement_text = 'پوشش سراسری اعزام تکنسین در کلیه شهرستان‌های مازندران';
    public string $landing_support_phone = '۰۱۱-۳۲۲۲۴۴۵۵';
    public bool $landing_show_tech_count = true;

    public function mount()
    {
        $this->map_provider               = FieldServiceSetting::get('map_provider', 'osm');
        $this->map_neshan_api_key         = FieldServiceSetting::get('map_neshan_api_key', '');
        $this->map_map_ir_api_key         = FieldServiceSetting::get('map_map_ir_api_key', '');
        $this->map_custom_tile_url        = FieldServiceSetting::get('map_custom_tile_url', '');
        $this->map_custom_api_key         = FieldServiceSetting::get('map_custom_api_key', '');
        $this->map_default_lat            = (float) FieldServiceSetting::get('map_default_lat', 36.5444);
        $this->map_default_lng            = (float) FieldServiceSetting::get('map_default_lng', 52.6780);
        $this->map_default_zoom           = (int) FieldServiceSetting::get('map_default_zoom', 13);

        $this->default_commission_percent = (float) FieldServiceSetting::get('default_commission_percent', 15.0);
        $this->base_travel_fee            = (float) FieldServiceSetting::get('base_travel_fee', 60000);
        $this->dispatch_timeout_seconds   = (int) FieldServiceSetting::get('dispatch_timeout_seconds', 45);
        $this->default_warranty_days      = (int) FieldServiceSetting::get('default_warranty_days', 90);

        $this->landing_announcement_text  = FieldServiceSetting::get('landing_announcement_text', 'پوشش سراسری اعزام تکنسین در کلیه شهرستان‌های مازندران');
        $this->landing_support_phone      = FieldServiceSetting::get('landing_support_phone', '۰۱۱-۳۲۲۲۴۴۵۵');
        $this->landing_show_tech_count    = (bool) FieldServiceSetting::get('landing_show_tech_count', true);

        // Coverage initialization
        $this->covered_provinces = FieldServiceSetting::getCoveredProvinces();
        $this->covered_cities    = FieldServiceSetting::getCoveredCities();
        $this->active_province_tab = $this->covered_provinces[0] ?? 'مازندران';
    }

    public function toggleProvince(string $province): void
    {
        if (in_array($province, $this->covered_provinces, true)) {
            if (count($this->covered_provinces) <= 1) {
                $this->dispatch('notify', ['type' => 'error', 'text' => 'حداقل یک استان باید در مناطق تحت پوشش فعال باشد.']);
                return;
            }
            $this->covered_provinces = array_values(array_diff($this->covered_provinces, [$province]));
            $provCities = ProvinceCity::getCities($province);
            $this->covered_cities = array_values(array_diff($this->covered_cities, $provCities));
            if ($this->active_province_tab === $province) {
                $this->active_province_tab = $this->covered_provinces[0] ?? 'مازندران';
            }
        } else {
            $this->covered_provinces[] = $province;
            $this->active_province_tab = $province;
            $provCities = ProvinceCity::getCities($province);
            $topCities = array_slice($provCities, 0, 4);
            $this->covered_cities = array_values(array_unique(array_merge($this->covered_cities, $topCities)));
        }
    }

    public function setActiveProvinceTab(string $province): void
    {
        $this->active_province_tab = $province;
        $this->city_search = '';
    }

    public function toggleCity(string $city): void
    {
        if (in_array($city, $this->covered_cities, true)) {
            if (count($this->covered_cities) <= 1) {
                $this->dispatch('notify', ['type' => 'error', 'text' => 'حداقل یک شهر باید در مناطق تحت پوشش فعال باشد.']);
                return;
            }
            $this->covered_cities = array_values(array_diff($this->covered_cities, [$city]));
        } else {
            $this->covered_cities[] = $city;
        }
    }

    public function selectAllCitiesForActiveProvince(): void
    {
        $cities = ProvinceCity::getCities($this->active_province_tab);
        $this->covered_cities = array_values(array_unique(array_merge($this->covered_cities, $cities)));
    }

    public function deselectAllCitiesForActiveProvince(): void
    {
        $cities = ProvinceCity::getCities($this->active_province_tab);
        $this->covered_cities = array_values(array_diff($this->covered_cities, $cities));
    }

    public function saveSettings()
    {
        FieldServiceSetting::set('map_provider', $this->map_provider, 'map');
        FieldServiceSetting::set('map_neshan_api_key', $this->map_neshan_api_key, 'map');
        FieldServiceSetting::set('map_map_ir_api_key', $this->map_map_ir_api_key, 'map');
        FieldServiceSetting::set('map_custom_tile_url', $this->map_custom_tile_url, 'map');
        FieldServiceSetting::set('map_custom_api_key', $this->map_custom_api_key, 'map');
        FieldServiceSetting::set('map_default_lat', $this->map_default_lat, 'map');
        FieldServiceSetting::set('map_default_lng', $this->map_default_lng, 'map');
        FieldServiceSetting::set('map_default_zoom', $this->map_default_zoom, 'map');

        // Save Coverage
        FieldServiceSetting::set('covered_provinces', $this->covered_provinces, 'coverage');
        FieldServiceSetting::set('covered_cities', $this->covered_cities, 'coverage');

        FieldServiceSetting::set('default_commission_percent', $this->default_commission_percent, 'financial');
        FieldServiceSetting::set('base_travel_fee', $this->base_travel_fee, 'financial');
        FieldServiceSetting::set('dispatch_timeout_seconds', $this->dispatch_timeout_seconds, 'dispatch');
        FieldServiceSetting::set('default_warranty_days', $this->default_warranty_days, 'financial');

        FieldServiceSetting::set('landing_announcement_text', $this->landing_announcement_text, 'frontend');
        FieldServiceSetting::set('landing_support_phone', $this->landing_support_phone, 'frontend');
        FieldServiceSetting::set('landing_show_tech_count', $this->landing_show_tech_count, 'frontend');

        $this->dispatch('notify', [
            'type' => 'success',
            'text' => 'تنظیمات سامانه خدمات فنی و مناطق تحت پوشش با موفقیت ذخیره شد.',
        ]);
    }

    public function render()
    {
        $allProvinces = ProvinceCity::getProvinces();
        $activeProvinceCities = ProvinceCity::getCities($this->active_province_tab);

        if (!empty(trim($this->city_search))) {
            $search = trim($this->city_search);
            $activeProvinceCities = array_filter($activeProvinceCities, fn($c) => str_contains($c, $search));
        }

        return view('fieldservice::livewire.admin.settings', [
            'allProvinces'         => $allProvinces,
            'activeProvinceCities' => $activeProvinceCities,
        ]);
    }
}
