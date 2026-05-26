<?php

namespace Modules\Market\App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Modules\Market\App\Models\Order;
use Modules\Settings\Entities\Setting;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;

class PaymentService
{
    protected array $settings;

    public function __construct()
    {
        $this->settings = Setting::all()->pluck('value', 'key')->toArray();
    }

    /**
     * Prepare data and redirect the user to the selected payment gateway.
     *
     * @param Order $order
     * @return mixed
     * @throws \Exception
     */
    public function redirectToGateway(Order $order)
    {
        $gateway = $order->payment_method;
        if (!$gateway || $gateway === 'online') {
            $gateway = $this->settings['default_payment_gateway'] ?? null;
        }

        if (!$gateway) {
            throw new \Exception('هیچ درگاه پرداخت پیش‌فرضی تنظیم نشده است.');
        }

        // Create an invoice
        $invoice = new Invoice;
        $amount = (int) ($order->grand_total ?: $order->total_amount);
        $invoice->amount($amount);
        $invoice->detail('description', 'پرداخت سفارش شماره ' . $order->id);
        $invoice->detail('order_id', $order->id);
        $mobile = ($order->shipping_address_json['recipient_mobile'] ?? null) ?: (optional($order->client)->mobile ?? null);
        $invoice->detail('mobile', $mobile);

        // Set the callback URL
        $callbackUrl = route('market.checkout.callback');

        // Purchase the invoice
        return Payment::via($gateway)->callbackUrl($callbackUrl)->purchase($invoice, function($driver, $transactionId) use ($order) {
            // Store transaction ID in the order
            $order->transaction_id = $transactionId;
            $order->save();
        })->pay()->render();
    }

    /**
     * Verify the payment after the user returns from the gateway.
     *
     * @param Request $request
     * @return Order|null
     */
    public function verifyPayment(Request $request): ?Order
    {
        try {
            $transactionId = $request->input('Authority'); // Zarinpal specific, may need adjustment
            $orderId = $request->input('order_id'); // Assuming it's passed back or stored in session

            // Find the order by transaction ID
            $order = Order::where('transaction_id', $transactionId)->firstOrFail();

            $gateway = $order->payment_method;
            if (!$gateway || $gateway === 'online') {
                $gateway = $this->settings['default_payment_gateway'] ?? null;
            }

            if (!$gateway) return null;

            // Verify the payment
            $amount = (int) ($order->grand_total ?: $order->total_amount);
            $receipt = Payment::via($gateway)
                ->amount($amount)
                ->transactionId($transactionId)
                ->verify();

            // Payment is successful, update order status
            $order->status = 'processing';
            $order->payment_ref_id = $receipt->getReferenceId();
            $order->paid_at = now();
            $order->save();

            // You can dispatch an event here like OrderPaid
            // event(new OrderPaid($order));

            return $order;

        } catch (\Exception $e) {
            // Log the error
            \Log::error('Payment Verification Failed: ' . $e->getMessage());

            // If order exists, you might want to mark it as 'failed'
            if (isset($order)) {
                $order->status = 'failed';
                $order->save();
                // Release the stock reservation
                (new StockService())->releaseReservation($order);
            }

            return null;
        }
    }
}
