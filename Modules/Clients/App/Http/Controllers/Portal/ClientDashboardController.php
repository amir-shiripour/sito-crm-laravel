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

        $isBookingActive  = $this->isModuleActive('Booking', 'appointments');
        $isMarketActive   = $this->isModuleActive('Market', 'market_orders');
        $isServicesActive = $this->isModuleActive('Services', 'service_invoices');
        $isRentalActive   = $this->isModuleActive('Properties', 'property_rental_bookings') && 
            (bool) (\Modules\Properties\Entities\PropertySetting::get('rental_mode_enabled', 0) ?: \Modules\Properties\Entities\PropertySetting::get('rental_system_enabled', 0));

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
        $rentalData          = $isRentalActive ? $this->getRentalData($client) : $this->getEmptyModuleData();

        // 4. ترکیب تمامی پرداخت‌ها و صورت‌حساب‌ها (خدمات + نوبت‌دهی + فروشگاه + اقامتگاه)
        $allPayments = collect()
            ->merge($servicesInvoiceData['payments'] ?? collect())
            ->merge($bookingData['payments'])
            ->merge($marketData['payments'])
            ->merge($rentalData['payments'])
            ->sortByDesc('date')
            ->values();

        // مبلغ پرداخت نشده کلی بر مبنای ریال برای نمایش در داشبورد
        $unpaidInvoicesSum = $allPayments->where('is_pending', true)->sum('amount_in_rials');
        $recentPayments = $allPayments
            ->reject(fn($payment) => in_array($payment->status, ['CANCELED', 'CANCELLED'], true))
            ->values()
            ->take(5);

        return view('clients::portal.dashboard', [
            'client'                  => $client,
            'showBookingFeatures'     => $isBookingActive,
            'showMarketFeatures'      => $isMarketActive,
            'showInvoiceFeatures'     => $isServicesActive,
            'showOrderFeatures'       => $isServicesActive,
            'showRentalFeatures'      => $isRentalActive,

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

            // داده‌های رزرو اقامتگاه
            'activeRentalBookingsCount' => $rentalData['activeCount'] ?? 0,
            'recentRentalBookings'      => $rentalData['recent'] ?? collect(),

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
                $rawStatus = strtoupper(trim((string)$payment->status));
                $normalizedStatus = match($rawStatus) {
                    'PAID', 'SUCCESS', 'COMPLETED' => 'PAID',
                    'CANCELED', 'CANCELLED', 'REJECTED' => 'CANCELED',
                    'FAILED', 'FAIL' => 'FAILED',
                    'REFUNDED' => 'REFUNDED',
                    default => 'PENDING',
                };

                $displayAmount = ($currencyUnit === 'IRT') ? ($payment->amount / 10) : $payment->amount;
                $currLabel = ($currencyUnit === 'IRT') ? 'تومان' : 'ریال';

                // Booking payment amounts in database are ALWAYS in Rials (IRR)
                $amountInRials = (float)$payment->amount;

                return (object)[
                    'id'             => $payment->id,
                    'type'           => 'booking',
                    'type_label'     => 'نوبت‌دهی',
                    'amount'         => $payment->amount,
                    'amount_display' => $displayAmount,
                    'amount_in_rials'=> $amountInRials,
                    'currency_label' => $currLabel,
                    'status'         => $normalizedStatus,
                    'date'           => $payment->created_at,
                    'is_pending'     => $normalizedStatus === 'PENDING',
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
                ->whereDoesntHave('status', function($q) {
                    $q->where('name', 'LIKE', '%لغو%')
                      ->orWhere('name', 'LIKE', '%ادغام%');
                })
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

                    $invCurrency = strtolower($inv->currency ?? \Modules\Settings\Entities\Setting::where('key', 'currency')->value('value') ?? 'rial');
                    $currLabel = in_array($invCurrency, ['rial', 'irr', 'ریال']) ? 'ریال' : 'تومان';
                    $rawAmount = $isPending ? ($remaining > 0 ? $remaining : $inv->total) : $inv->total;
                    $amountInRials = in_array($invCurrency, ['toman', 'tmn', 'تومان']) ? ($rawAmount * 10) : $rawAmount;

                    return (object)[
                        'id'             => $inv->id,
                        'type'           => 'service',
                        'type_label'     => 'فاکتور خدمات',
                        'amount'         => $inv->total,
                        'amount_display' => $rawAmount,
                        'amount_in_rials'=> $amountInRials,
                        'currency_label' => $currLabel,
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
                ->with(['service', 'status', 'invoice.status', 'invoice.items'])
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

                $marketCurrency = strtolower(\Modules\Market\Entities\MarketSetting::getValue('general.currency', 'rial'));
                $currLabel = in_array($marketCurrency, ['rial', 'irr', 'ریال']) ? 'ریال' : 'تومان';
                $rawAmount = (float)$order->grand_total;
                $amountInRials = in_array($marketCurrency, ['toman', 'tmn', 'تومان']) ? ($rawAmount * 10) : $rawAmount;

                return (object)[
                    'id'             => $order->id,
                    'type'           => 'market',
                    'type_label'     => 'فروشگاه',
                    'amount'         => $order->grand_total,
                    'amount_display' => $rawAmount,
                    'amount_in_rials'=> $amountInRials,
                    'currency_label' => $currLabel,
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
     * واکشی اطلاعات مربوط به ماژول رزرو اقامتگاه
     */
    private function getRentalData($client): array
    {
        $activeCount = 0;
        $recent = collect();
        $payments = collect();

        if (class_exists(\Modules\Properties\Entities\PropertyRentalBooking::class)) {
            $activeCount = \Modules\Properties\Entities\PropertyRentalBooking::where('client_id', $client->id)
                ->whereIn('status', [
                    \Modules\Properties\Entities\PropertyRentalBooking::STATUS_PENDING_HOST,
                    \Modules\Properties\Entities\PropertyRentalBooking::STATUS_AWAITING_PAYMENT,
                    \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CONFIRMED,
                    \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CHECKED_IN,
                ])
                ->count();

            $recent = \Modules\Properties\Entities\PropertyRentalBooking::with(['property.primaryImage', 'host'])
                ->where('client_id', $client->id)
                ->latest()
                ->take(5)
                ->get();

            $bookings = \Modules\Properties\Entities\PropertyRentalBooking::where('client_id', $client->id)
                ->where('status', '!=', \Modules\Properties\Entities\PropertyRentalBooking::STATUS_PENDING_HOST)
                ->latest()
                ->take(15)
                ->get();

            $currency = \Modules\Properties\Entities\PropertySetting::get('rental_currency', 'IRT');
            $currLabel = $currency === 'IRR' ? 'ریال' : 'تومان';

            $payments = $bookings->map(function ($booking) use ($currency, $currLabel) {
                $bookingStatus = strtolower(trim((string)$booking->status));
                $paymentStatus = strtolower(trim((string)$booking->payment_status));

                $normalizedStatus = 'PENDING';
                if ($paymentStatus === 'paid' || in_array($bookingStatus, ['confirmed', 'checked_in', 'completed'])) {
                    $normalizedStatus = 'PAID';
                } elseif (str_starts_with($bookingStatus, 'cancel') || in_array($bookingStatus, ['rejected', 'expired'])) {
                    $normalizedStatus = 'CANCELED';
                } else {
                    $normalizedStatus = 'PENDING';
                }

                $rawAmount = (float) $booking->total_price;
                $amountInRials = in_array(strtolower($currency), ['toman', 'irt', 'تومان']) ? ($rawAmount * 10) : $rawAmount;
                $isPending = ($normalizedStatus === 'PENDING');

                return (object)[
                    'id'             => $booking->id,
                    'type'           => 'rental',
                    'type_label'     => 'رزرو اقامتگاه',
                    'amount'         => $booking->total_price,
                    'amount_display' => $rawAmount,
                    'amount_in_rials'=> $amountInRials,
                    'currency_label' => $currLabel,
                    'status'         => $normalizedStatus,
                    'date'           => $booking->created_at,
                    'is_pending'     => $isPending,
                    'booking_code'   => $booking->booking_code,
                    'url'            => route('client.payments.show', ['type' => 'rental', 'id' => $booking->id]),
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
