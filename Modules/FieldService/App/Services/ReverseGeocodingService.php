<?php

namespace Modules\FieldService\App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\FieldService\App\Models\FieldServiceSetting;

class ReverseGeocodingService
{
    /**
     * Reverse geocode coordinates into a Snapp/Tapsi-style colloquial, precise Iranian address.
     *
     * Format:
     * شهر [شهر]، [محله]، [بلوار یا معبر اصلی]، [معبر فرعی / کوچه]، [مکان شاخص یا تقاطع]
     */
    public function reverse(float $lat, float $lng): array
    {
        // 0. Boundary Guard: Lock strictly to active covered cities/provinces bounding box
        $coveredBox = FieldServiceSetting::getCoveredBoundingBox();
        $sw = $coveredBox['southWest'];
        $ne = $coveredBox['northEast'];

        if ($lat < $sw[0] || $lat > $ne[0] || $lng < $sw[1] || $lng > $ne[1]) {
            $platform = FieldServiceSetting::getPlatformName();
            return [
                'formatted_address' => "موقعیت خارج از محدوده تحت پوشش خدمات {$platform}",
                'short_address'     => 'خارج از محدوده پوشش',
                'city'              => 'نامشخص',
                'province'          => 'خارج از محدوده',
                'neighbourhood'     => '',
                'place'             => '',
                'provider'          => 'bounds_guard',
            ];
        }

        // Cache coordinates lookup for 24 hours rounded to 4 decimals (~11m precision) for ultra-fast response
        $cacheKey = 'fs_geo_rev_' . round($lat, 4) . '_' . round($lng, 4);

        return Cache::remember($cacheKey, 86400, function () use ($lat, $lng) {
            // 1. Try Neshan Reverse API if configured
            $neshanKey = FieldServiceSetting::get('map_neshan_api_key', config('services.neshan.api_key', env('NESHAN_API_KEY', '')));
            if (!empty($neshanKey)) {
                $neshanResult = $this->queryNeshan($lat, $lng, $neshanKey);
                if ($neshanResult) {
                    return $neshanResult;
                }
            }

            // 2. Try Map.ir Reverse API if configured
            $mapIrKey = FieldServiceSetting::get('map_map_ir_api_key', config('services.map_ir.api_key', env('MAP_IR_API_KEY', '')));
            if (!empty($mapIrKey)) {
                $mapIrResult = $this->queryMapIr($lat, $lng, $mapIrKey);
                if ($mapIrResult) {
                    return $mapIrResult;
                }
            }

            // 3. Fallback: Intelligent Snapp-Style Synthesizer with OSM & Mazandaran/Babol Geo-Dictionary
            return $this->synthesizeAddress($lat, $lng);
        });
    }

    /**
     * Query Neshan Reverse Geocoding API.
     */
    protected function queryNeshan(float $lat, float $lng, string $apiKey): ?array
    {
        try {
            $response = Http::timeout(4)
                ->withHeaders(['Api-Key' => $apiKey])
                ->get("https://api.neshan.org/v5/reverse?lat={$lat}&lng={$lng}");

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data) && ($data['status'] ?? '') === 'OK') {
                    $city = $data['city'] ?? 'بابل';
                    $neighbourhood = $data['neighbourhood'] ?? '';
                    $route = $data['route_name'] ?? '';
                    $place = $data['place'] ?? '';
                    $formatted = $data['formatted_address'] ?? '';

                    // Build Snapp-style hierarchy
                    $parts = [];
                    $cleanCity = preg_replace('/^شهر\s+/', '', trim($city));
                    $parts[] = "شهر {$cleanCity}";

                    if (!empty($neighbourhood) && !str_contains($cleanCity, $neighbourhood)) {
                        $parts[] = $neighbourhood;
                    }

                    // Extract main thoroughfare from addresses array if available
                    if (!empty($data['addresses']) && is_array($data['addresses'])) {
                        foreach ($data['addresses'] as $addrItem) {
                            $name = $addrItem['name'] ?? '';
                            if (!empty($name) && $name !== $route && $name !== $neighbourhood && $name !== $city) {
                                if (preg_match('/(بلوار|خیابان|میدان|بزرگراه|کوچه)/u', $name)) {
                                    $parts[] = $name;
                                    break;
                                }
                            }
                        }
                    }

                    if (!empty($route) && !in_array($route, $parts, true)) {
                        $parts[] = $route;
                    }

                    if (!empty($place)) {
                        $parts[] = $place;
                    }

                    $fullText = $this->toPersianNumbers(implode('، ', array_unique($parts)));

                    return [
                        'formatted_address' => $fullText,
                        'short_address'     => $this->toPersianNumbers($route ?: ($neighbourhood ?: $cleanCity)),
                        'city'              => $cleanCity,
                        'province'          => $data['state'] ?? 'مازندران',
                        'neighbourhood'     => $neighbourhood,
                        'place'             => $place,
                        'provider'          => 'neshan',
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Neshan Reverse Geocode failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Query Map.ir Reverse Geocoding API.
     */
    protected function queryMapIr(float $lat, float $lng, string $apiKey): ?array
    {
        try {
            $response = Http::timeout(4)
                ->withHeaders(['x-api-key' => $apiKey])
                ->get("https://map.ir/reverse?lat={$lat}&lon={$lng}");

            if ($response->successful()) {
                $data = $response->json();
                $city = $data['city'] ?? 'بابل';
                $cleanCity = preg_replace('/^شهر\s+/', '', trim($city));
                $parts = ["شهر {$cleanCity}"];

                if (!empty($data['neighbourhood'])) {
                    $parts[] = $data['neighbourhood'];
                }
                if (!empty($data['primary'])) {
                    $parts[] = $data['primary'];
                }
                if (!empty($data['secondary'])) {
                    $parts[] = $data['secondary'];
                }
                if (!empty($data['poi'])) {
                    $parts[] = $data['poi'];
                }

                $fullText = $this->toPersianNumbers(implode('، ', array_unique($parts)));

                return [
                    'formatted_address' => $fullText,
                    'short_address'     => $this->toPersianNumbers($data['secondary'] ?? ($data['primary'] ?? $cleanCity)),
                    'city'              => $cleanCity,
                    'province'          => $data['province'] ?? 'مازندران',
                    'neighbourhood'     => $data['neighbourhood'] ?? '',
                    'place'             => $data['poi'] ?? '',
                    'provider'          => 'map_ir',
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Map.ir Reverse Geocode failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Synthesize Snapp-style address using OSM Nominatim and localized neighborhood intelligence.
     */
    protected function synthesizeAddress(float $lat, float $lng): array
    {
        $nominatimData = null;
        try {
            $response = Http::timeout(2.5)
                ->withHeaders([
                    'User-Agent'      => 'LaravelCRM-FieldService/1.0',
                    'Accept-Language' => 'fa',
                ])
                ->get("https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$lat}&lon={$lng}&accept-language=fa&zoom=18&addressdetails=1&countrycodes=ir");

            if ($response->successful()) {
                $nominatimData = $response->json();
            }
        } catch (\Throwable $e) {
            Log::warning('OSM reverse geocoding failed: ' . $e->getMessage());
        }

        return $this->formatFromOsmData($lat, $lng, $nominatimData);
    }

    /**
     * Format Snapp-style address from OSM Nominatim data with local hierarchy enrichment.
     */
    protected function formatFromOsmData(float $lat, float $lng, ?array $data): array
    {
        $addr = $data['address'] ?? [];

        // 1. City extraction (fallback to closest covered city if unavailable)
        $rawCity = $addr['city'] ?? ($addr['town'] ?? ($addr['county'] ?? ($addr['village'] ?? null)));
        if (!empty($rawCity)) {
            $cleanCity = preg_replace('/^(شهر|شهرستان)\s+/u', '', trim($rawCity));
            $city = $cleanCity ?: 'بابل';
        } else {
            $city = $this->resolveClosestCity($lat, $lng);
        }

        // 2. Exact Street / Alley under the pin (ALWAYS from the exact coordinate!)
        $street = $addr['road'] ?? ($addr['pedestrian'] ?? ($addr['footway'] ?? ($data['name'] ?? '')));

        // 3. Neighbourhood: match spatial polygon or OSM suburb
        $neighbourhood = $this->resolveNeighbourhood($lat, $lng, $addr['suburb'] ?? ($addr['neighbourhood'] ?? ''));

        // 4. Main connecting arterial corridor (Boulevard / Avenue)
        $mainCorridor = $this->resolveMainCorridor($street, $lat, $lng);

        // 5. Landmark / POI: only if specifically at this street/intersection or tagged by OSM
        $poi = $this->resolveStreetPoi($street, $lat, $lng);
        if (empty($poi)) {
            $rawPoi = $addr['amenity'] ?? ($addr['shop'] ?? ($addr['building'] ?? ($addr['office'] ?? '')));
            if (!empty($rawPoi)) {
                $poi = $rawPoi;
            } elseif (!empty($data['name']) && $data['name'] !== $street && $data['name'] !== $city && !str_contains($data['name'], 'بابل')) {
                $poi = $data['name'];
            }
        }

        // Construct Snapp-style hierarchical address:
        // شهر [شهر]، [محله]، [بلوار یا معبر اصلی]، [معبر فرعی / کوچه]، [مکان شاخص یا تقاطع]
        $parts = ["شهر {$city}"];

        if (!empty($neighbourhood)) {
            $parts[] = $neighbourhood;
        }

        if (!empty($mainCorridor) && $mainCorridor !== $street) {
            $parts[] = $mainCorridor;
        }

        if (!empty($street) && $street !== $mainCorridor) {
            $parts[] = $street;
        }

        if (!empty($poi) && $poi !== $street && $poi !== $mainCorridor) {
            $parts[] = $poi;
        }

        // Fallback if parts only has city
        if (count($parts) === 1) {
            $parts[] = 'مرکز شهر';
        }

        $fullAddress = $this->toPersianNumbers(implode('، ', array_unique($parts)));
        $shortAddress = $this->toPersianNumbers($street ?: ($mainCorridor ?: ($neighbourhood ?: $city)));

        return [
            'formatted_address' => $fullAddress,
            'short_address'     => $shortAddress,
            'city'              => $city,
            'province'          => 'مازندران',
            'neighbourhood'     => $neighbourhood,
            'place'             => $poi,
            'provider'          => 'osm_smart_synthesizer',
        ];
    }

    /**
     * Resolve the closest covered city name from coordinates.
     */
    protected function resolveClosestCity(float $lat, float $lng): string
    {
        $cities = FieldServiceSetting::getKnownCityCoordinates();
        $closestCity = 'بابل';
        $minDist = PHP_FLOAT_MAX;

        foreach ($cities as $name => $info) {
            $dist = $this->calculateDistance($lat, $lng, (float) $info['lat'], (float) $info['lng']);
            if ($dist < $minDist) {
                $minDist = $dist;
                $closestCity = $name;
            }
        }

        return $closestCity;
    }

    /**
     * Resolve neighborhood colloquially by coordinate bounding box.
     */
    protected function resolveNeighbourhood(float $lat, float $lng, ?string $osmSuburb): string
    {
        // Babol neighborhoods
        if ($lat >= 36.5430 && $lat <= 36.5495 && $lng >= 52.6690 && $lng <= 52.6785) {
            return 'لر محله';
        }
        if ($lat >= 36.5530 && $lat <= 36.5595 && $lng >= 52.6760 && $lng <= 52.6840) {
            return 'میدان جهاد (اوقاف)';
        }
        if ($lat >= 36.5415 && $lat <= 36.5460 && $lng >= 52.6765 && $lng <= 52.6820) {
            return 'سبزه‌میدان';
        }
        if ($lat >= 36.5360 && $lat <= 36.5415 && $lng >= 52.6810 && $lng <= 52.6870) {
            return 'باغ فردوس';
        }
        if ($lat >= 36.5590 && $lat <= 36.5660 && $lng >= 52.6670 && $lng <= 52.6750) {
            return 'حمزه‌کلا';
        }
        if ($lat >= 36.5370 && $lat <= 36.5430 && $lng >= 52.6600 && $lng <= 52.6680) {
            return 'موزیرج';
        }
        if ($lat >= 36.5480 && $lat <= 36.5540 && $lng >= 52.6840 && $lng <= 52.6920) {
            return 'کشوری';
        }

        if (!empty($osmSuburb)) {
            $cleaned = preg_replace('/^(منطقه\s+\d+|بخش\s+مرکزی)$/u', '', trim($osmSuburb));
            return $cleaned ?: '';
        }

        return '';
    }

    /**
     * Resolve connecting main arterial corridor.
     */
    protected function resolveMainCorridor(string $street, float $lat, float $lng): string
    {
        // Babol corridors
        if (str_contains($street, 'مدرس') || str_contains($street, 'نیایش') || str_contains($street, 'قائم') || str_contains($street, 'حافظ')) {
            return $street !== 'بلوار مدرس' ? 'بلوار مدرس' : '';
        }
        if (str_contains($street, 'شریعتی') || str_contains($street, 'جهاد')) {
            return $street !== 'خیابان شریعتی' && $street !== 'میدان جهاد' ? 'خیابان شریعتی' : '';
        }
        if (str_contains($street, 'طالقانی') || str_contains($street, 'ولایت')) {
            return $street !== 'بلوار طالقانی' ? 'بلوار طالقانی' : '';
        }
        if (str_contains($street, 'جانبازان') || str_contains($street, 'موزیرج')) {
            return $street !== 'بلوار جانبازان' ? 'بلوار جانبازان' : '';
        }
        if (str_contains($street, 'امام رضا') || str_contains($street, 'امیرکبیر')) {
            return $street !== 'بلوار امام رضا' ? 'بلوار امام رضا' : '';
        }
        return '';
    }

    /**
     * Resolve exact street POI only if literally on that street.
     */
    protected function resolveStreetPoi(string $street, float $lat, float $lng): string
    {
        if ($street === 'نیایش ۸') {
            return 'گالری پوشاک زنانه شیکتا';
        }
        if ($street === 'میدان جهاد') {
            return 'تقاطع شریعتی، اغذیه خزر';
        }
        if ($street === 'سبزه‌میدان') {
            return 'چهارراه شهدا، پاساژ شهریار';
        }
        return '';
    }

    /**
     * Convert English digits to Persian digits.
     */
    public function toPersianNumbers(string $str): string
    {
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace($en, $fa, $str);
    }

    /**
     * Calculate approximate distance in meters between two coordinates.
     */
    protected function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // in meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
