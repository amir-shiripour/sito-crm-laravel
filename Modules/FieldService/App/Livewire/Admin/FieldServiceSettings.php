<?php

namespace Modules\FieldService\App\Livewire\Admin;

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

        FieldServiceSetting::set('default_commission_percent', $this->default_commission_percent, 'financial');
        FieldServiceSetting::set('base_travel_fee', $this->base_travel_fee, 'financial');
        FieldServiceSetting::set('dispatch_timeout_seconds', $this->dispatch_timeout_seconds, 'dispatch');
        FieldServiceSetting::set('default_warranty_days', $this->default_warranty_days, 'financial');

        FieldServiceSetting::set('landing_announcement_text', $this->landing_announcement_text, 'frontend');
        FieldServiceSetting::set('landing_support_phone', $this->landing_support_phone, 'frontend');
        FieldServiceSetting::set('landing_show_tech_count', $this->landing_show_tech_count, 'frontend');

        $this->dispatch('notify', [
            'type' => 'success',
            'text' => 'تنظیمات سامانه نصاب‌باشی با موفقیت ذخیره شد.',
        ]);
    }

    public function render()
    {
        return view('fieldservice::livewire.admin.settings');
    }
}
