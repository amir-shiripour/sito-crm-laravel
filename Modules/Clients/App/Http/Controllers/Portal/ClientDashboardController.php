<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Modules\Market\App\Models\Order as MarketOrder;
use Modules\Services\App\Http\Models\Invoice as ServiceInvoice;
use Modules\Services\App\Http\Models\Order as ServiceOrder;
use Nwidart\Modules\Facades\Module;

class ClientDashboardController extends Controller
{
    public function index()
    {
        $client = auth('client')->user();

        // 1. بررسی اصولی فعال بودن ماژول‌ها
        $isBookingActive  = $this->isModuleActive('Booking', 'appointments');
        $isMarketActive   = $this->isModuleActive('Market', 'market_orders');
        $isServicesActive = $this->isModuleActive('Services', 'service_invoices');

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

        // 3. واکشی داده‌های هر ماژول از طریق متدهای مجزا
        $bookingData         = $isBookingActive ? $this->getBookingData($client, $bookingCurrencyUnit) : $this->getEmptyModuleData();
        $marketData          = $isMarketActive  ? $this->getMarketData($client)  : $this->getEmptyModuleData();
        $servicesInvoiceData = $isServicesActive ? $this->getServicesInvoiceData($client) : $this->getEmptyModuleData();
        $servicesOrderData   = $isServicesActive ? $this->getServicesOrderData($client) : $this->getEmptyModuleData();

        // 4. ترکیب تمامی پرداخت‌ها و صورت‌حساب‌ها (خدمات + نوبت‌دهی + فروشگاه)
        $allPayments = collect()
            ->merge($servicesInvoiceData['payments'] ?? collect())
            ->merge($bookingData['payments'])
            ->merge($marketData['payments'])
            ->sortByDesc('date')
            ->values();

        // مبلغ پرداخت نشده کلی برای نمایش در داشبورد
        $unpaidInvoicesSum = $allPayments->where('is_pending', true)->sum('amount_display');
        $recentPayments = $allPayments->take(5);

        return view('clients::portal.dashboard', [
            'client'                  => $client,
            'showBookingFeatures'     => $isBookingActive,
            'showMarketFeatures'      => $isMarketActive,
            'showInvoiceFeatures'     => $isServicesActive,
            'showOrderFeatures'       => $isServicesActive,

            // واحد پول برای نمایش داینامیک نوبت‌دهی
            'bookingCurrencyUnit'     => $bookingCurrencyUnit,
            'bookingCurrencyLabel'    => $bookingCurrencyLabel,

            // داده‌های نوبت‌دهی
            'activeAppointmentsCount' => $bookingData['activeCount'],
            'recentAppointments'      => $bookingData['recent'],

            // داده‌های فروشگاه
            'activeMarketOrdersCount' => $marketData['activeCount'],
            'recentMarketOrders'      => $marketData['recent'],

            // داده‌های فاکتورهای خدمات
            'unpaidInvoicesCount'     => $servicesInvoiceData['unpaidCount'] ?? 0,
            'totalInvoicesCount'      => $servicesInvoiceData['totalCount'] ?? 0,
            'recentInvoices'          => $servicesInvoiceData['recent'] ?? collect(),

            // داده‌های سفارش‌های خدمات
            'activeOrdersCount'       => $servicesOrderData['activeCount'] ?? 0,
            'totalOrdersCount'        => $servicesOrderData['totalCount'] ?? 0,
            'recentOrders'            => $servicesOrderData['recent'] ?? collect(),

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
                $displayAmount = ($currencyUnit === 'IRT') ? ($payment->amount / 10) : $payment->amount;
                $currLabel = ($currencyUnit === 'IRT') ? 'تومان' : 'ریال';

                return (object)[
                    'id'             => $payment->id,
                    'type'           => 'booking',
                    'type_label'     => 'نوبت‌دهی',
                    'amount'         => $payment->amount,
                    'amount_display' => $displayAmount,
                    'currency_label' => $currLabel,
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
     * واکشی اطلاعات مربوط به فاکتورهای خدمات
     */
    private function getServicesInvoiceData($client): array
    {
        $unpaidCount = 0;
        $totalCount  = 0;
        $recent      = collect();
        $payments    = collect();

        if (class_exists(ServiceInvoice::class) && Schema::hasTable('service_invoices')) {
            $totalCount = ServiceInvoice::where('customer_id', $client->id)
                ->whereNotNull('invoice_number')
                ->count();

            $unpaidCount = ServiceInvoice::where('customer_id', $client->id)
                ->whereNotNull('invoice_number')
                ->whereHas('status', function($q) {
                    $q->where('name', 'LIKE', '%انتظار%')
                      ->orWhere('name', 'LIKE', '%معوقه%');
                })
                ->count();

            $recent = ServiceInvoice::where('customer_id', $client->id)
                ->whereNotNull('invoice_number')
                ->with(['service', 'status', 'items'])
                ->latest('id')
                ->take(5)
                ->get();

            $payments = ServiceInvoice::where('customer_id', $client->id)
                ->whereNotNull('invoice_number')
                ->with('status')
                ->get()
                ->map(function($inv) {
                    $statusName = $inv->status?->name ?? '';
                    $isPaid = str_contains($statusName, 'پرداخت شده');
                    $isPending = str_contains($statusName, 'انتظار') || str_contains($statusName, 'معوقه');
                    $isCanceled = str_contains($statusName, 'لغو') || str_contains($statusName, 'ادغام');

                    $normalizedStatus = $isPaid ? 'PAID' : ($isCanceled ? 'CANCELED' : 'PENDING');
                    $remaining = max(0, $inv->total - $inv->paid_amount);

                    return (object)[
                        'id'             => $inv->id,
                        'type'           => 'service',
                        'type_label'     => 'فاکتور خدمات',
                        'amount'         => $inv->total,
                        'amount_display' => $isPending ? ($remaining > 0 ? $remaining : $inv->total) : $inv->total,
                        'currency_label' => 'تومان',
                        'status'         => $normalizedStatus,
                        'date'           => $inv->issue_date ?? $inv->created_at,
                        'is_pending'     => $isPending,
                    ];
                });
        }

        return [
            'unpaidCount' => $unpaidCount,
            'totalCount'  => $totalCount,
            'recent'      => $recent,
            'payments'    => $payments,
        ];
    }

    /**
     * واکشی اطلاعات مربوط به سفارش‌های خدمات
     */
    private function getServicesOrderData($client): array
    {
        $activeCount = 0;
        $totalCount  = 0;
        $recent      = collect();

        if (class_exists(ServiceOrder::class) && Schema::hasTable('service_orders')) {
            $totalCount = ServiceOrder::where('customer_id', $client->id)->count();

            $activeCount = ServiceOrder::where('customer_id', $client->id)
                ->whereHas('status', function($q) {
                    $q->where('name', 'LIKE', '%فعال%')->where('name', 'NOT LIKE', '%غیر%');
                })
                ->count();

            $recent = ServiceOrder::where('customer_id', $client->id)
                ->with(['service', 'status', 'invoice.status'])
                ->latest('id')
                ->take(5)
                ->get();
        }

        return [
            'activeCount' => $activeCount,
            'totalCount'  => $totalCount,
            'recent'      => $recent,
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

        if (class_exists(MarketOrder::class) && Schema::hasTable('market_orders')) {
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
                    'amount_display' => $order->grand_total,
                    'currency_label' => 'تومان',
                    'status'         => $normalizedStatus,
                    'date'           => $order->created_at,
                    'is_pending'     => in_array(strtolower($normalizedStatus), ['pending', 'unpaid']),
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
