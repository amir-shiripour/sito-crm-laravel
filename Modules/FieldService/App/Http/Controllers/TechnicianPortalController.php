<?php

namespace Modules\FieldService\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Models\FieldServiceSetting;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Services\MapProviderService;
use Spatie\Permission\Models\Role;

class TechnicianPortalController extends Controller
{
    public function index(MapProviderService $mapProvider): View
    {
        $mapConfig = $mapProvider->getMapConfig();
        $coveredCities = FieldServiceSetting::getCoveredCitiesWithCoords();

        // Resolve authenticated user & linked technician record
        $user = auth()->user();
        $technician = null;

        if ($user) {
            $technician = Technician::where('user_id', $user->id)->first();

            // Fallback match by mobile if user_id was not linked yet
            if (!$technician && !empty($user->mobile)) {
                $technician = Technician::where('mobile', $user->mobile)->first();
                if ($technician) {
                    $technician->user_id = $user->id;
                    $technician->save();

                    // Assign role
                    $role = Role::firstOrCreate(['name' => 'technician', 'guard_name' => 'web'], [
                        'display_name' => 'متخصص و تکنسین فنی',
                    ]);
                    if (!$user->hasRole('technician')) {
                        $user->assignRole($role);
                    }
                }
            }
        }

        $activeMission = null;
        $availableMissions = collect();
        $todayStats = [
            'completed_count' => 0,
            'net_earnings'    => 0,
            'rating'          => 5.0,
            'acceptance_rate' => 100,
        ];

        // If authenticated as a valid technician, load their cockpit data
        if ($technician) {
            // Guarantee coordinates for map placement (defaults to their operational city or Bابل)
            if (!$technician->lat || !$technician->lng) {
                $cityData = $coveredCities[$technician->city] ?? ['lat' => 36.5444, 'lng' => 52.6780];
                $technician->lat = $cityData['lat'] ?? 36.5444;
                $technician->lng = $cityData['lng'] ?? 52.6780;
            }

            // 1. Active Mission
            $activeMission = ServiceOrder::where('technician_id', $technician->id)
                ->whereIn('status', [
                    OrderStatus::ACCEPTED,
                    OrderStatus::EN_ROUTE,
                    OrderStatus::ARRIVED,
                    OrderStatus::IN_PROGRESS,
                    OrderStatus::INVOICE_PENDING
                ])
                ->with(['client', 'category', 'invoice.items'])
                ->latest()
                ->first();

            // 2. Available Missions Filtered by Technician's Skills, City, and Operational Radius
            if ($technician->is_online) {
                $techCategoryIds = $technician->categories()->pluck('field_service_categories.id')->toArray();
                
                $missionsQuery = ServiceOrder::whereIn('status', [OrderStatus::SEARCHING, OrderStatus::DISPATCHED])
                    ->whereNull('technician_id')
                    ->with(['client', 'category'])
                    ->latest();

                if (!empty($techCategoryIds)) {
                    $missionsQuery->whereIn('category_id', $techCategoryIds);
                }

                if (!empty($technician->city)) {
                    $missionsQuery->where(function ($q) use ($technician) {
                        $q->where('city', $technician->city)
                          ->orWhereNull('city');
                    });
                }

                $availableMissions = $missionsQuery->take(12)->get();

                // Calculate distance to technician for each available mission and filter by operational radius
                $techLat = (float) $technician->lat;
                $techLng = (float) $technician->lng;
                $maxRadius = (int) ($technician->operational_radius_km ?: 25);

                $availableMissions = $availableMissions->map(function ($order) use ($techLat, $techLng) {
                    if ($order->lat && $order->lng) {
                        $order->distance_km = self::haversineDistance($techLat, $techLng, (float) $order->lat, (float) $order->lng);
                    } else {
                        $order->distance_km = null;
                    }
                    return $order;
                })->filter(function ($order) use ($maxRadius) {
                    return is_null($order->distance_km) || $order->distance_km <= $maxRadius;
                })->take(8)->values();
            } else {
                $availableMissions = collect();
            }

            // 3. Today's stats
            $todayOrders = ServiceOrder::where('technician_id', $technician->id)
                ->whereDate('completed_at', today())
                ->where('status', OrderStatus::COMPLETED)
                ->with('invoice')
                ->get();

            $todayCompletedCount = $todayOrders->count();
            $todayEarnings = $todayOrders->sum(function ($order) {
                return $order->invoice ? ($order->invoice->total_amount * 0.85) : 0;
            });

            $todayStats = [
                'completed_count' => $todayCompletedCount,
                'net_earnings'    => round($todayEarnings),
                'rating'          => $technician->rating_avg ?: 5.0,
                'acceptance_rate' => $technician->acceptance_rate ?: 100,
            ];
        }

        return view('fieldservice::technician.index', compact(
            'mapConfig',
            'technician',
            'activeMission',
            'availableMissions',
            'todayStats'
        ));
    }

    public function logout(): RedirectResponse
    {
        $user = auth()->user();
        if ($user) {
            $tech = Technician::where('user_id', $user->id)->first();
            if ($tech) {
                $tech->is_online = false;
                $tech->save();
            }
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        return redirect()->route('fieldservice.technician');
    }

    /**
     * Calculate Great Circle distance between two coordinates in Kilometers
     */
    private static function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 1);
    }
}
