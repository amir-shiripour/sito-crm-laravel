<?php

namespace Modules\Booking\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Modules\Booking\Entities\BookingService;

class OnlineBookingController extends Controller
{
    public function index()
    {
        $services = BookingService::query()
            ->where('status', BookingService::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        return view('booking::web.index', compact('services'));
    }

    public function service(BookingService $service)
    {
        $service->load(['serviceProviders.provider']);
        return view('booking::web.service', compact('service'));
    }
}
