<?php

namespace Modules\Market\App\Services\Map;

use Illuminate\Support\Facades\Http;
use Modules\Market\Entities\MarketSetting;

class NeshanMapService implements MapServiceInterface
{
    public function getProviderName(): string
    {
        return 'neshan';
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
                'Api-Key' => $apiKey
            ])->timeout(5)->get("https://api.neshan.org/v5/reverse", [
                'lat' => $lat,
                'lng' => $lng
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'province' => $data['state'] ?? '',
                    'city' => $data['city'] ?? '',
                    'address' => $data['formatted_address'] ?? '',
                    'formatted' => $data['formatted_address'] ?? '',
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
            'formatted' => 'خطا در ارتباط با سرور نقشه نشان'
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
                'term' => $term,
            ];
            if ($lat !== null && $lng !== null) {
                $params['lat'] = $lat;
                $params['lng'] = $lng;
            }

            $response = Http::withHeaders([
                'Api-Key' => $apiKey
            ])->timeout(5)->get("https://api.neshan.org/v1/search", $params);

            if ($response->successful()) {
                $data = $response->json();
                $results = [];
                foreach ($data['results'] ?? [] as $item) {
                    $results[] = [
                        'title' => $item['title'] ?? '',
                        'address' => $item['address'] ?? '',
                        'lat' => (float)($item['location']['y'] ?? 0),
                        'lng' => (float)($item['location']['x'] ?? 0),
                    ];
                }
                return $results;
            }
        } catch (\Exception $e) {
            // Log
        }

        return [];
    }
}
