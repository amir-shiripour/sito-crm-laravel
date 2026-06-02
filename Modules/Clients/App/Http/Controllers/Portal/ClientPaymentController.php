<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\Market\App\Models\Order as MarketOrder; // 💡 مسیر صحیح جایگزین شد

class ClientPaymentController extends Controller
{
    public function index()
    {
        $client = auth('client')->user();
        $allPayments = collect();

        // 1. Fetch Booking Payments
        if (class_exists(\Modules\Booking\Entities\BookingPayment::class) && Schema::hasTable('booking_payments')) {
            $bookingPayments = \Modules\Booking\Entities\BookingPayment::whereHas('appointment', function($q) use ($client) {
                $q->where('client_id', $client->id);
            })->with('appointment.service')->get()->map(function($payment) {
                return (object)[
                    'id' => $payment->id,
                    'ref_id' => $payment->id,
                    'type' => 'booking',
                    'type_label' => 'نوبت‌دهی (رزرو)',
                    'title' => 'پرداخت برای رزرو ' . optional(optional($payment->appointment)->service)->name,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'date' => $payment->created_at,
                    'payment_record' => $payment
                ];
            });
            $allPayments = $allPayments->merge($bookingPayments);
        }

        // 2. Fetch Market Orders (as invoices/payments)
        if (class_exists(MarketOrder::class) && Schema::hasTable('market_orders')) {
            $marketOrders = MarketOrder::where('client_id', $client->id)->get()->map(function($order) {
                $statusMap = [
                    'pending' => 'PENDING',
                    'paid' => 'PAID',
                    'failed' => 'FAILED',
                    'refunded' => 'REFUNDED',
                    'canceled' => 'CANCELED',
                    'unpaid'   => 'PENDING',
                ];
                $normalizedStatus = $statusMap[strtolower($order->payment_status)] ?? strtoupper($order->payment_status);

                return (object)[
                    'id' => $order->id,
                    'ref_id' => $order->id,
                    'type' => 'market',
                    'type_label' => 'سفارش فروشگاه',
                    'title' => 'سفارش خرید #' . $order->id,
                    'amount' => $order->grand_total,
                    'status' => $normalizedStatus,
                    'date' => $order->created_at,
                    'payment_record' => $order
                ];
            });
            $allPayments = $allPayments->merge($marketOrders);
        }

        // Sort by date descending
        $allPayments = $allPayments->sortByDesc('date')->values();

        return view('clients::portal.payments.index', compact('allPayments'));
    }

    public function show($type, $id)
    {
        $client = auth('client')->user();

        if ($type === 'booking') {
            if (!class_exists(\Modules\Booking\Entities\BookingPayment::class) || !Schema::hasTable('booking_payments')) abort(404);

            $payment = \Modules\Booking\Entities\BookingPayment::whereHas('appointment', function($q) use ($client) {
                $q->where('client_id', $client->id);
            })->with(['appointment.service', 'appointment.provider'])->findOrFail($id);

            return view('clients::portal.payments.show_booking', compact('payment'));
        }

        if ($type === 'market') {
            if (!class_exists(MarketOrder::class) || !Schema::hasTable('market_orders')) abort(404);

            $order = MarketOrder::where('client_id', $client->id)
                ->with(['items'])
                ->findOrFail($id);

            return view('clients::portal.payments.show_market', compact('order'));
        }

        abort(404);
    }

    public function marketOrdersIndex()
    {
        $client = auth('client')->user();
        if (!class_exists(MarketOrder::class) || !Schema::hasTable('market_orders')) abort(404);

        $orders = MarketOrder::where('client_id', $client->id)->latest()->paginate(15);
        return view('clients::portal.orders.index', compact('orders'));
    }

    public function marketOrderShow($id)
    {
        $client = auth('client')->user();
        if (!class_exists(MarketOrder::class) || !Schema::hasTable('market_orders')) abort(404);

        $order = MarketOrder::where('client_id', $client->id)
            ->with(['items.vendorProduct.variant', 'items.vendor'])
            ->findOrFail($id);

        return view('clients::portal.orders.show', compact('order'));
    }
}
