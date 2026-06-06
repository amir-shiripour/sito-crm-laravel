<?php

namespace Modules\Booking\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Modules\Booking\Entities\BookingCategory;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingSetting;

class CureController extends Controller
{
    public function index()
    {
        $settings = BookingSetting::current();

        // All active services with category, ordered by name
        $services = BookingService::query()
            ->where('status', BookingService::STATUS_ACTIVE)
            ->with('category')
            ->orderBy('name')
            ->get();

        // Flatten to plain arrays safe for @js() serialisation
        $servicesJs = $services->map(fn ($s) => [
            'id'            => $s->id,
            'name'          => $s->name,
            'base_price'    => (float) $s->base_price,
            'category_id'   => $s->category_id,
            'category_name' => optional($s->category)->name,
            'custom_prices' => $s->custom_prices ?? ['tabs' => []],
        ])->values();

        // All categories for the filter pills
        $categories = BookingCategory::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $recentAppointments = collect();
        foreach ([
                     \Modules\Booking\Entities\BookingAppointment::class,
                     \Modules\Booking\Models\BookingAppointment::class,
                 ] as $class) {
            if (class_exists($class)) {
                $recentAppointments = $class::query()
                    ->with(['service:id,name', 'client:id,name'])
                    ->orderByDesc('created_at')
                    ->limit(20)
                    ->get();
                break;
            }
        }

        return view('booking::user.cure.index', compact(
            'services',
            'servicesJs',
            'categories',
            'settings',
            'recentAppointments',
        ));
    }
}
