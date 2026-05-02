<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

class ClientDashboardController extends Controller
{
    public function index()
    {
        $client = auth('client')->user();

        $activeAppointmentsCount = 0;
        $unpaidInvoicesSum = 0;
        $recentAppointments = collect();
        $recentPayments = collect();

        if (class_exists(\Modules\Booking\Entities\Appointment::class)) {
            $activeAppointmentsCount = \Modules\Booking\Entities\Appointment::where('client_id', $client->id)
                ->whereIn('status', [
                    \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED,
                    \Modules\Booking\Entities\Appointment::STATUS_PENDING,
                    \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT,
                    \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED
                ])
                ->count();

            $recentAppointments = \Modules\Booking\Entities\Appointment::where('client_id', $client->id)
                ->with(['service', 'provider'])
                ->orderBy('id', 'desc')
                ->take(5)
                ->get();
        }

        // Unified Payments logic for Dashboard
        $allPayments = collect();

        if (class_exists(\Modules\Booking\Entities\BookingPayment::class)) {
            $bookingPayments = \Modules\Booking\Entities\BookingPayment::whereHas('appointment', function($q) use ($client) {
                $q->where('client_id', $client->id);
            })->with('appointment.service')->get()->map(function($payment) {
                return (object)[
                    'id' => $payment->id,
                    'type' => 'booking',
                    'type_label' => 'نوبت‌دهی',
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'date' => $payment->created_at,
                    'is_pending' => $payment->status === 'PENDING',
                ];
            });
            $allPayments = $allPayments->merge($bookingPayments);
        }

        if (class_exists(\Modules\Market\Entities\Order::class)) {
            $marketOrders = \Modules\Market\Entities\Order::where('client_id', $client->id)->get()->map(function($order) {
                $statusMap = [
                    'pending' => 'PENDING',
                    'paid' => 'PAID',
                    'failed' => 'FAILED',
                    'refunded' => 'REFUNDED',
                    'canceled' => 'CANCELED'
                ];
                $normalizedStatus = $statusMap[strtolower($order->payment_status)] ?? strtoupper($order->payment_status);

                return (object)[
                    'id' => $order->id,
                    'type' => 'market',
                    'type_label' => 'فروشگاه',
                    'amount' => $order->grand_total,
                    'status' => $normalizedStatus,
                    'date' => $order->created_at,
                    'is_pending' => strtolower($normalizedStatus) === 'pending',
                ];
            });
            $allPayments = $allPayments->merge($marketOrders);
        }

        // Calculate sum of unpaid invoices
        $unpaidInvoicesSum = $allPayments->where('is_pending', true)->sum('amount');

        // Get 5 most recent payments
        $recentPayments = $allPayments->sortByDesc('date')->take(5)->values();

        return view('clients::portal.dashboard', compact(
            'client',
            'activeAppointmentsCount',
            'unpaidInvoicesSum',
            'recentAppointments',
            'recentPayments'
        ));
    }
}
