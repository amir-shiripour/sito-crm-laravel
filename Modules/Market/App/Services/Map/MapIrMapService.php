<?php

namespace Modules\Market\App\Services\Map;

use Illuminate\Support\Facades\Http;
use Modules\Market\Entities\MarketSetting;

class MapIrMapService implements MapServiceInterface
{
    public function getProviderName(): string
    {
        return 'map_ir';
    }

    public function getApiKey(): ?string
    {
        return MarketSetting::getValue('map.api_key');
    }

    public function reverseGeocode(float $lat, float $lng): array
    {
        $apiKey = $this->getApiKey();
        if (empty($apiKey)) {
            return [
                'province' => '',
                'city' => '',
                'address' => '',
                'formatted' => 'تنظیمات کلید نقشه (API Key) وارد نشده است.'
            ];
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey
            ])->timeout(5)->get("https://map.ir/reverse", [
                'lat' => $lat,
                'lon' => $lng
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'province' => $data['province'] ?? '',
                    'city' => $data['city'] ?? '',
                    'address' => $data['address_compact'] ?? $data['postal_address'] ?? $data['address'] ?? '',
                    'formatted' => $data['address_compact'] ?? $data['address'] ?? '',
                    'data' => $data
                ];
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return [
            'province' => '',
            'city' => '',
            'address' => '',
            'formatted' => 'خطا در ارتباط با سرور نقشه map.ir'
        ];
    }

    public function search(string $term, ?float $lat = null, ?float $lng = null): array
    {
        $apiKey = $this->getApiKey();
        if (empty($apiKey) || empty($term)) {
            return [];
        }

        try {
            $params = [
                'text' => $term,
            ];
            if ($lat !== null && $lng !== null) {
                $params['lat'] = $lat;
                $params['lon'] = $lng;
            }

            $response = Http::withHeaders([
                'x-api-key' => $apiKey
            ])->timeout(5)->get("https://map.ir/search/v2/autocomplete", $params);

            if ($response->successful()) {
                $data = $response->json();
                $results = [];
                $items = $data['value'] ?? $data ?? [];
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $coords = $item['geom']['coordinates'] ?? [0, 0];
                        $results[] = [
                            'title' => $item['title'] ?? '',
                            'address' => $item['address'] ?? '',
                            'lat' => (float)($coords[1] ?? 0),
                            'lng' => (float)($coords[0] ?? 0),
                        ];
                    }
                }
                return $results;
            }
        } catch (\Exception $e) {
            // Log
        }

        return [];
    }
}
