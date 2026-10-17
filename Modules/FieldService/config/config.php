<?php

return [
    'name' => 'FieldService',
    'brand_name' => 'نصاب‌باشی',

    /*
    |--------------------------------------------------------------------------
    | Financial & Commission Settings
    |--------------------------------------------------------------------------
    | Default platform commission cut is 15%, 85% goes to the technician.
    | Base travel fee is in Rials (e.g. 600,000 IRR = 60,000 Tomans).
    */
    'default_commission_percent' => 15,
    'base_travel_fee'            => 600000,
    'default_warranty_days'      => 90,

    /*
    |--------------------------------------------------------------------------
    | Snapp-like Dispatch & Dynamic Radius Expansion
    |--------------------------------------------------------------------------
    | dispatch_timeout_seconds: Acceptance countdown timer per technician.
    | radius_expansion_km: Step-by-step radius expansion if not accepted.
    */
    'dispatch_timeout_seconds'   => 45,
    'radius_expansion_km'        => [3, 7, 15, 30, 60],

    /*
    |--------------------------------------------------------------------------
    | Interactive Map Configuration
    |--------------------------------------------------------------------------
    | Configurable Map Tile Providers:
    | - 'osm': OpenStreetMap (free, default)
    | - 'neshan': Neshan Map API
    | - 'map_ir': Map.ir
    | - 'custom': Custom XYZ tile template
    */
    'map' => [
        'default_provider' => env('FIELD_SERVICE_MAP_PROVIDER', 'osm'),
        'default_lat'      => (float) env('FIELD_SERVICE_MAP_LAT', 36.5444), // بابل - مازندران
        'default_lng'      => (float) env('FIELD_SERVICE_MAP_LNG', 52.6780),
        'default_zoom'     => (int) env('FIELD_SERVICE_MAP_ZOOM', 13),
        'providers' => [
            'osm' => [
                'name'      => 'OpenStreetMap',
                'tile_url'  => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                'subdomains'=> ['a', 'b', 'c'],
                'max_zoom'  => 19,
            ],
            'neshan' => [
                'name'      => 'نشان (Neshan)',
                'tile_url'  => 'https://api.neshan.org/v2/tiles/osm/{z}/{x}/{y}.png',
                'api_key'   => env('NESHAN_API_KEY', ''),
                'max_zoom'  => 19,
            ],
            'map_ir' => [
                'name'      => 'مپ (Map.ir)',
                'tile_url'  => 'https://map.ir/shiveh/xyz/1.0.0/clark@2x/{z}/{x}/{y}.png',
                'api_key'   => env('MAP_IR_API_KEY', ''),
                'max_zoom'  => 19,
            ],
            'custom' => [
                'name'      => 'تایل‌سرور سفارشی',
                'tile_url'  => env('CUSTOM_MAP_TILE_URL', ''),
                'api_key'   => env('CUSTOM_MAP_API_KEY', ''),
                'max_zoom'  => 19,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Workflows Integration Events
    |--------------------------------------------------------------------------
    */
    'workflow_events' => [
        'order_created'       => 'fieldservice.order.created',
        'order_dispatched'    => 'fieldservice.order.dispatched',
        'order_accepted'      => 'fieldservice.order.accepted',
        'technician_arrived'  => 'fieldservice.technician.arrived',
        'invoice_submitted'   => 'fieldservice.invoice.submitted',
        'invoice_paid'        => 'fieldservice.invoice.paid',
        'order_completed'     => 'fieldservice.order.completed',
        'backup_requested'    => 'fieldservice.backup.requested',
        'emergency_dispatched'=> 'fieldservice.emergency.dispatched',
    ],
];
