<?php

namespace Modules\FieldService\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FieldService\App\Models\FieldServiceSetting;

class FieldServiceDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(FieldServiceDefaultCategoriesSeeder::class);

        // Seed default operational settings only (no sample/test records)
        FieldServiceSetting::set('map_provider', 'osm', 'map');
        FieldServiceSetting::set('map_default_lat', '36.5444', 'map');
        FieldServiceSetting::set('map_default_lng', '52.6780', 'map');
        FieldServiceSetting::set('map_default_zoom', '13', 'map');
        FieldServiceSetting::set('default_commission_percent', '15', 'financial');
        FieldServiceSetting::set('base_travel_fee', '600000', 'financial');
        FieldServiceSetting::set('dispatch_timeout_seconds', '45', 'dispatch');
        FieldServiceSetting::set('radius_expansion_km', [3, 7, 15, 30, 60], 'dispatch');
    }
}
