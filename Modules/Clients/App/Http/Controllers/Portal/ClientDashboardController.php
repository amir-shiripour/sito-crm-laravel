<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Modules\Market\App\Models\Order as MarketOrder; // 💡 مسیر صحیح جایگزین شد
use Nwidart\Modules\Facades\Module;

class ClientDashboardController extends Controller
{
    public function index()
    {
        $client = auth('client')->user();

        // 1. بررسی اصولی فعال بودن ماژول‌ها (به جای نوشتن منطق در View)
        $isBookingActive = $this->isModuleActive('Booking', 'appointments');
        $isMarketActive  = $this->isModuleActive('Market', 'market_orders');

        // 2. واکشی داده‌های هر ماژول از طریق متدهای مجزا (تمیز نگه داشتن متد اصلی)
        $bookingData = $isBookingActive ? $this->getBookingData($client) : $this->getEmptyModuleData();
        $marketData  = $isMarketActive  ? $this->getMarketData($client)  : $this->getEmptyModuleData();

        // 3. ترکیب تمامی پرداخت‌ها (نوبت‌دهی + فروشگاه)
        $allPayments = collect()
            ->merge($bookingData['payments'])
            ->merge($marketData['payments'])
            ->sortByDesc('date')
            ->values();

        $unpaidInvoicesSum = $allPayments->where('is_pending', true)->sum('amount');
        $recentPayments = $allPayments->take(5);

        return view('clients::portal.dashboard', [
            'client'                  => $client,
            'showBookingFeatures'     => $isBookingActive,
            'showMarketFeatures'      => $isMarketActive, // ارسال به View برای جلوگیری از نوشتن منطق در Blade

            // داده‌های نوبت‌دهی
            'activeAppointmentsCount' => $bookingData['activeCount'],
            'recentAppointments'      => $bookingData['recent'],

            // داده‌های فروشگاه
            'activeMarketOrdersCount' => $marketData['activeCount'],
            'recentMarketOrders'      => $marketData['recent'],

            // داده‌های مالی ترکیبی
            'unpaidInvoicesSum'       => $unpaidInvoicesSum,
            'recentPayments'          => $recentPayments,
        ]);
    }

    /**
     * بررسی فعال بودن یک ماژول و وجود جدول آن
     */
    private function isModuleActive(string $moduleName, string $tableName): bool
    {
        return class_exists('\Nwidart\Modules\Facades\Module') &&
            Module::has($moduleName) &&
            Module::isEnabled($moduleName) &&
            Schema::hasTable($tableName);
    }

    /**
     * واکشی اطلاعات مربوط به ماژول نوبت‌دهی
     */
    private function getBookingData($client): array
    {
        $activeCount = 0;
        $recent = collect();
        $payments = collect();

        if (class_exists(\Modules\Booking\Entities\Appointment::class)) {
            $activeCount = \Modules\Booking\Entities\Appointment::where('client_id', $client->id)
                ->whereIn('status', [
                    \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED,
                    \Modules\Booking\Entities\Appointment::STATUS_PENDING,
                    \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT,
                    \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED
                ])
                ->count();

            $recent = \Modules\Booking\Entities\Appointment::where('client_id', $client->id)
                ->with(['service', 'provider'])
                ->orderBy('id', 'desc')
                ->take(5)
                ->get();
        }

        if (class_exists(\Modules\Booking\Entities\BookingPayment::class) && Schema::hasTable('booking_payments')) {
            $payments = \Modules\Booking\Entities\BookingPayment::whereHas('appointment', function($q) use ($client) {
                $q->where('client_id', $client->id);
            })->with('appointment.service')->get()->map(function($payment) {
                return (object)[
                    'id'         => $payment->id,
                    'type'       => 'booking',
                    'type_label' => 'نوبت‌دهی',
                    'amount'     => $payment->amount,
                    'status'     => $payment->status,
                    'date'       => $payment->created_at,
                    'is_pending' => $payment->status === 'PENDING',
                ];
            });
        }

        return [
            'activeCount' => $activeCount,
            'recent'      => $recent,
            'payments'    => $payments,
        ];
    }

    /**
     * واکشی اطلاعات مربوط به ماژول فروشگاه
     */
    private function getMarketData($client): array
    {
        $activeCount = 0;
        $recent = collect();
        $payments = collect();

        // 💡 از کلاس MarketOrder که در بالای فایل import شده استفاده می‌کنیم
        $activeCount = MarketOrder::where('client_id', $client->id)
            ->whereIn('payment_status', ['pending', 'processing', 'wait_for_payment', 'unpaid'])
            ->count();

        $recent = MarketOrder::where('client_id', $client->id)
            ->latest()
            ->take(5)
            ->get();

        $payments = MarketOrder::where('client_id', $client->id)->get()->map(function($order) {
            $statusMap = [
                'pending'  => 'PENDING',
                'paid'     => 'PAID',
                'failed'   => 'FAILED',
                'refunded' => 'REFUNDED',
                'canceled' => 'CANCELED',
                'unpaid'   => 'PENDING',
            ];
            $normalizedStatus = $statusMap[strtolower($order->payment_status)] ?? strtoupper($order->payment_status);

            return (object)[
                'id'         => $order->id,
                'type'       => 'market',
                'type_label' => 'فروشگاه',
                'amount'     => $order->grand_total,
                'status'     => $normalizedStatus,
                'date'       => $order->created_at,
                'is_pending' => in_array(strtolower($normalizedStatus), ['pending', 'unpaid']),
            ];
        });


        return [
            'activeCount' => $activeCount,
            'recent'      => $recent,
            'payments'    => $payments,
        ];
    }

    /**
     * ساختار خالی در صورتی که ماژول مربوطه غیرفعال باشد
     */
    private function getEmptyModuleData(): array
    {
        return [
            'activeCount' => 0,
            'recent'      => collect(),
            'payments'    => collect(),
        ];
    }
}
