<?php

namespace Modules\FieldService\App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RoutingService
{
    /**
     * Get road routing between two coordinates (origin to destination).
     *
     * @param float $originLat
     * @param float $originLng
     * @param float $destLat
     * @param float $destLng
     * @return array
     */
    public function getRoute(float $originLat, float $originLng, float $destLat, float $destLng): array
    {
        $cacheKey = sprintf('fs_route_%s_%s_%s_%s', round($originLat, 4), round($originLng, 4), round($destLat, 4), round($destLng, 4));

        return Cache::remember($cacheKey, 120, function () use ($originLat, $originLng, $destLat, $destLng) {
            try {
                // OSRM expects coordinates in lng,lat order
                $url = sprintf(
                    'https://router.project-osrm.org/route/v1/driving/%s,%s;%s,%s?overview=full&geometries=geojson',
                    $originLng,
                    $originLat,
                    $destLng,
                    $destLat
                );

                $response = Http::timeout(5)
                    ->withHeaders(['User-Agent' => 'LaravelCRM-RoutingService/1.0'])
                    ->get($url);

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['code'] ?? '') === 'Ok' && !empty($data['routes'][0]['geometry']['coordinates'])) {
                        $rawCoords = $data['routes'][0]['geometry']['coordinates']; // array of [lng, lat]
                        
                        // Convert GeoJSON [lng, lat] to Leaflet [lat, lng]
                        $leafletCoords = array_map(function ($pt) {
                            return [(float)$pt[1], (float)$pt[0]];
                        }, $rawCoords);

                        $distanceMeters = (float) ($data['routes'][0]['distance'] ?? 0);
                        $durationSeconds = (float) ($data['routes'][0]['duration'] ?? 0);
                        $distanceKm = round($distanceMeters / 1000, 1);
                        $etaMinutes = max(2, (int) round($durationSeconds / 60));

                        return [
                            'success'     => true,
                            'coordinates' => $leafletCoords,
                            'distance_km' => $distanceKm,
                            'eta_minutes' => $etaMinutes,
                            'eta_text'    => "حدود {$etaMinutes} دقیقه",
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('OSRM route calculation error: ' . $e->getMessage());
            }

            // Graceful fallback to direct calculation if routing service is unreachable
            $dist = round($this->haversine($originLat, $originLng, $destLat, $destLng) * 1.25, 1);
            $eta = max(2, (int) round(($dist / 25) * 60));

            return [
                'success'     => false,
                'coordinates' => [
                    [$originLat, $originLng],
                    [$destLat, $destLng],
                ],
                'distance_km' => $dist,
                'eta_minutes' => $eta,
                'eta_text'    => "حدود {$eta} دقیقه",
            ];
        });
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
