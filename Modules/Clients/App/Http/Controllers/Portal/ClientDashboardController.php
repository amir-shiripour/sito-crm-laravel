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

        // 2. دریافت تنظیمات واحد پول نوبت‌دهی برای نمایش صحیح مبالغ
        $bookingCurrencyUnit  = 'IRR';
        $bookingCurrencyLabel = 'ریال';
        if ($isBookingActive && class_exists(\Modules\Booking\Entities\BookingSetting::class) && Schema::hasTable('booking_settings')) {
            try {
                $bs = \Modules\Booking\Entities\BookingSetting::current();
                $bookingCurrencyUnit  = $bs->currency_unit ?? 'IRR';
                $bookingCurrencyLabel = $bookingCurrencyUnit === 'IRT' ? 'تومان' : 'ریال';
            } catch (\Exception $e) {}
        }

        // 3. واکشی داده‌های هر ماژول از طریق متدهای مجزا (تمیز نگه داشتن متد اصلی)
        $bookingData = $isBookingActive ? $this->getBookingData($client, $bookingCurrencyUnit) : $this->getEmptyModuleData();
        $marketData  = $isMarketActive  ? $this->getMarketData($client)  : $this->getEmptyModuleData();

        // 4. ترکیب تمامی پرداخت‌ها (نوبت‌دهی + فروشگاه)
        $allPayments = collect()
            ->merge($bookingData['payments'])
            ->merge($marketData['payments'])
            ->sortByDesc('date')
            ->values();

        // مبلغ پرداخت نشده باید با توجه به واحد محاسبه شود
        // مبالغ booking همیشه به IRR در DB هستند پس amount_display را استفاده می‌کنیم
        $unpaidInvoicesSum = $allPayments->where('is_pending', true)->sum('amount_display');
        $recentPayments = $allPayments->take(5);

        return view('clients::portal.dashboard', [
            'client'                  => $client,
            'showBookingFeatures'     => $isBookingActive,
            'showMarketFeatures'      => $isMarketActive,

            // واحد پول برای نمایش داینامیک
            'bookingCurrencyUnit'     => $bookingCurrencyUnit,
            'bookingCurrencyLabel'    => $bookingCurrencyLabel,

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
    private function getBookingData($client, string $currencyUnit = 'IRR'): array
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
            })->with('appointment.service')->get()->map(function($payment) use ($currencyUnit) {
                // booking_payments.amount همیشه به ریال (IRR) ذخیره شده
                // amount_display: مقداری که باید به کاربر نشان داده شود (IRT = تقسیم بر 10)
                $displayAmount = ($currencyUnit === 'IRT') ? ($payment->amount / 10) : $payment->amount;
                $currLabel = ($currencyUnit === 'IRT') ? 'تومان' : 'ریال';

                return (object)[
                    'id'             => $payment->id,
                    'type'           => 'booking',
                    'type_label'     => 'نوبت‌دهی',
                    'amount'         => $payment->amount,         // مقدار خام ریالی در DB
                    'amount_display' => $displayAmount,          // مقدار قابل نمایش برای کاربر
                    'currency_label' => $currLabel,              // برچسب واحد تنظیمات
                    'status'         => $payment->status,
                    'date'           => $payment->created_at,
                    'is_pending'     => $payment->status === 'PENDING',
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
                'id'             => $order->id,
                'type'           => 'market',
                'type_label'     => 'فروشگاه',
                'amount'         => $order->grand_total,
                'amount_display' => $order->grand_total,  // سفارش‌ها به واحد خودشان ذخیره شده‌اند
                'currency_label' => 'تومان',               // واحد ثابت فروشگاه
                'status'         => $normalizedStatus,
                'date'           => $order->created_at,
                'is_pending'     => in_array(strtolower($normalizedStatus), ['pending', 'unpaid']),
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
