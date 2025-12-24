<?php

namespace Modules\Booking\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingPayment;

class BookingDashboardController extends Controller
{
    public function index()
    {
        $now = now('UTC');
        $from = $now->copy()->subDays(30);

        $total = Appointment::query()->where('start_at_utc', '>=', $from)->count();
        $confirmed = Appointment::query()->where('status', Appointment::STATUS_CONFIRMED)->where('start_at_utc', '>=', $from)->count();
        $canceled = Appointment::query()->where('status', 'like', 'CANCELED%')->where('start_at_utc', '>=', $from)->count();
        $noShow = Appointment::query()->where('status', Appointment::STATUS_NO_SHOW)->where('start_at_utc', '>=', $from)->count();

        $revenue = BookingPayment::query()->where('status', BookingPayment::STATUS_PAID)->sum('amount');

        return view('booking::user.dashboard', compact('total', 'confirmed', 'canceled', 'noShow', 'revenue'));
    }
}
