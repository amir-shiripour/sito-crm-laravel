<?php

namespace Modules\Market\App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Modules\Market\App\Models\Order;
use Modules\Settings\Entities\Setting;
use App\Services\PaymentService as GlobalPaymentService;

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

        $globalPaymentService = new GlobalPaymentService($gateway);

        $amount = (float) ($order->grand_total ?: $order->total_amount);
        $description = 'پرداخت سفارش شماره ' . $order->id;
        $email = optional($order->client)->email;
        $mobile = ($order->shipping_address_json['recipient_mobile'] ?? null) ?: (optional($order->client)->mobile ?? null);
        
        // Pass order_id in callback so we can find the order during callback
        $callbackUrl = route('market.checkout.callback', ['order_id' => $order->id]);

        $result = $globalPaymentService->requestPayment(
            $amount,
            $description,
            $email,
            $mobile,
            $callbackUrl
        );

        if ($result['success']) {
            // Store transaction ID / authority in the order
            $order->transaction_id = $result['authority'];
            $order->save();

            // Redirect the user to the gateway URL
            return Redirect::away($result['payment_url']);
        } else {
            throw new \Exception($result['message'] ?? 'خطا در ارتباط با درگاه پرداخت');
        }
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
            $orderId = $request->query('order_id') ?: $request->input('order_id');
            if ($orderId) {
                $order = Order::findOrFail($orderId);
            } else {
                // Fallback to finding by transaction ID / Authority / trackId
                $authority = $request->input('Authority') ?: $request->input('trackId');
                if (!$authority) {
                    throw new \Exception('شناسه تراکنش یافت نشد.');
                }
                $order = Order::where('transaction_id', $authority)->firstOrFail();
            }

            $gateway = $order->payment_method;
            if (!$gateway || $gateway === 'online') {
                $gateway = $this->settings['default_payment_gateway'] ?? null;
            }

            if (!$gateway) return null;

            $globalPaymentService = new GlobalPaymentService($gateway);

            // Prepare verification data based on gateway
            $amount = (float) ($order->grand_total ?: $order->total_amount);
            $verifyData = [
                'Amount' => $amount,
            ];

            if ($gateway === 'zarinpal') {
                $verifyData['Authority'] = $request->input('Authority');
                $verifyData['Status'] = $request->input('Status');
            } elseif ($gateway === 'zibal') {
                $verifyData['trackId'] = $request->input('trackId');
                $verifyData['success'] = $request->input('success');
            }

            $result = $globalPaymentService->verifyPayment($verifyData);

            if ($result['success']) {
                $method = $order->payment_method;
                $paymentStatus = \Modules\Market\Entities\MarketSetting::getValue("orders.status_{$method}_payment", 'paid');
                $deliveryStatus = \Modules\Market\Entities\MarketSetting::getValue("orders.status_{$method}_delivery", 'processing');

                // Payment is successful, update order status dynamically
                $order->payment_status = $paymentStatus;
                $order->delivery_status = $deliveryStatus;
                $order->payment_ref_id = $result['ref_id'] ?? null;
                $order->paid_at = now();
                $order->save();

                return $order;
            } else {
                throw new \Exception($result['message'] ?? 'تایید پرداخت ناموفق بود.');
            }

        } catch (\Exception $e) {
            // Log the error
            \Log::error('Payment Verification Failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            // Mark order as failed and release stock
            if (isset($order)) {
                $order->payment_status = 'failed';
                $order->delivery_status = 'canceled';
                $order->save();
                
                // Release the stock reservation
                (new StockService())->releaseReservation($order);
            }

            return null;
        }
    }
}
