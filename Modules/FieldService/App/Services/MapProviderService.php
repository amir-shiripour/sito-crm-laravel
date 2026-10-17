<?php

namespace Modules\FieldService\App\Services;

use Modules\FieldService\App\Models\FieldServiceSetting;

class MapProviderService
{
    /**
     * Get the active map configuration for client-side Leaflet initialization.
     */
    public function getMapConfig(): array
    {
        $providerKey = FieldServiceSetting::get('map_provider', config('fieldservice.map.default_provider', 'osm'));
        $defaultLat  = (float) FieldServiceSetting::get('map_default_lat', config('fieldservice.map.default_lat', 36.5444));
        $defaultLng  = (float) FieldServiceSetting::get('map_default_lng', config('fieldservice.map.default_lng', 52.6780));
        $defaultZoom = (int) FieldServiceSetting::get('map_default_zoom', config('fieldservice.map.default_zoom', 13));

        $providers = config('fieldservice.map.providers', []);
        $providerData = $providers[$providerKey] ?? $providers['osm'];

        $tileUrl = FieldServiceSetting::get("map_{$providerKey}_tile_url", $providerData['tile_url'] ?? 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
        $apiKey  = FieldServiceSetting::get("map_{$providerKey}_api_key", $providerData['api_key'] ?? '');

        // Inject api_key into tile URL if needed (e.g. for Map.ir or Neshan if parameterized)
        if (!empty($apiKey) && str_contains($tileUrl, '{api_key}')) {
            $tileUrl = str_replace('{api_key}', $apiKey, $tileUrl);
        }

        return [
            'provider'   => $providerKey,
            'name'       => $providerData['name'] ?? 'OpenStreetMap',
            'tile_url'   => $tileUrl,
            'api_key'    => $apiKey,
            'subdomains' => $providerData['subdomains'] ?? ['a', 'b', 'c'],
            'max_zoom'   => $providerData['max_zoom'] ?? 19,
            'center'     => [
                'lat' => $defaultLat,
                'lng' => $defaultLng,
            ],
            'zoom'       => $defaultZoom,
        ];
    }
}
