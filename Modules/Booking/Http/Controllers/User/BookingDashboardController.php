<?php

namespace Modules\Booking\Http\Controllers\User;

use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingPayment;
use Modules\Booking\Entities\BookingService;
use Morilog\Jalali\Jalalian;

class BookingDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $timezone = $user->timezone ?? config('app.timezone');
        $now = now($timezone);
        $from = $now->copy()->subDays(30);
        $utcFrom = (clone $from)->setTimezone('UTC');
        $utcNow = now('UTC');

        // Base query for user's appointments
        $appointmentsQuery = Appointment::query()
            ->when(!$user->hasRole('super-admin'), function ($q) use ($user) {
                // Providers see their own appointments
                $q->where('provider_user_id', $user->id);
            });

        // KPIs for the last 30 days
        $appointmentsLast30Days = (clone $appointmentsQuery)->where('start_at_utc', '>=', $utcFrom)->get();

        $total = $appointmentsLast30Days->count();
        $confirmed = $appointmentsLast30Days->where('status', Appointment::STATUS_CONFIRMED)->count();
        $canceled = $appointmentsLast30Days->filter(fn($a) => substr($a->status, 0, 8) === 'CANCELED')->count();
        $noShow = $appointmentsLast30Days->where('status', Appointment::STATUS_NO_SHOW)->count();
        $pending = $appointmentsLast30Days->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_PENDING_PAYMENT])->count();

        $todaysAppointmentsCount = (clone $appointmentsQuery)
            ->whereBetween('start_at_utc', [$utcNow->copy()->startOfDay(), $utcNow->copy()->endOfDay()])
            ->count();

        $revenue = BookingPayment::query()
            ->where('status', BookingPayment::STATUS_PAID)
            ->whereHas('appointment', function($q) use ($utcFrom, $user) {
                $q->where('start_at_utc', '>=', $utcFrom)
                  ->when(!$user->hasRole('super-admin'), fn($q) => $q->where('provider_user_id', $user->id));
            })
            ->sum('amount');

        // Chart Data: Appointments per day for the last 30 days
        // Grouping in PHP to avoid MySQL CONVERT_TZ issues on some local servers (like Laragon)
        $appointmentsForChart = (clone $appointmentsQuery)
            ->where('start_at_utc', '>=', $utcFrom)
            ->get(['start_at_utc']);

        $appointmentsChart = [];
        $hasVerta = class_exists(Jalalian::class);

        foreach ($appointmentsForChart as $app) {
            if ($app->start_at_utc) {
                $localDateObj = $app->start_at_utc->copy()->setTimezone($timezone);
                if ($hasVerta) {
                    $localDate = Jalalian::fromDateTime($localDateObj)->format('Y-m-d');
                } else {
                    $localDate = $localDateObj->format('Y-m-d');
                }

                if (!isset($appointmentsChart[$localDate])) {
                    $appointmentsChart[$localDate] = 0;
                }
                $appointmentsChart[$localDate]++;
            }
        }

        $chartLabels = [];
        $chartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $dateObj = $now->copy()->subDays($i);

            if ($hasVerta) {
                $jDate = Jalalian::fromDateTime($dateObj);
                $dateStr = $jDate->format('Y-m-d');
                $chartLabels[] = $jDate->format('m/d');
            } else {
                $dateStr = $dateObj->format('Y-m-d');
                $chartLabels[] = $dateObj->format('M d');
            }

            $chartData[] = $appointmentsChart[$dateStr] ?? 0;
        }

        // Status Distribution for Pie Chart
        $statusDistribution = [
            'تایید شده' => $confirmed,
            'لغو شده' => $canceled,
            'عدم حضور' => $noShow,
            'در انتظار' => $pending,
            'انجام شده' => $appointmentsLast30Days->where('status', Appointment::STATUS_DONE)->count(),
        ];
        $statusDistribution = array_filter($statusDistribution);

        // Top 5 Services
        $topServices = BookingService::query()
            ->withCount(['appointments' => fn($q) =>
                $q->where('start_at_utc', '>=', $utcFrom)
                  ->when(!$user->hasRole('super-admin'), fn($q) => $q->where('provider_user_id', $user->id))
            ])
            ->orderBy('appointments_count', 'desc')
            ->take(5)
            ->get();

        // Upcoming 5 Appointments
        $upcomingAppointments = (clone $appointmentsQuery)
            ->with(['service', 'client', 'provider'])
            ->where('start_at_utc', '>', $utcNow)
            ->orderBy('start_at_utc', 'asc')
            ->take(5)
            ->get();

        return view('booking::user.dashboard', compact(
            'total', 'confirmed', 'canceled', 'noShow', 'revenue', 'pending', 'todaysAppointmentsCount',
            'chartLabels', 'chartData', 'statusDistribution',
            'topServices', 'upcomingAppointments'
        ));
    }
}
