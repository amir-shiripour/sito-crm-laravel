<?php

namespace Modules\Market\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Market\App\Models\Order;
use Modules\Market\App\Services\PaymentService;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('market::web.checkout.index');
    }

    /**
     * Process the order and redirect to the payment gateway or show a success page.
     *
     * @param Order $order
     * @param PaymentService $paymentService
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     */
    public function process(Order $order, PaymentService $paymentService)
    {
        // Ensure the user owns the order
        if ($order->client_id !== auth('client')->id()) {
            abort(403, 'شما اجازه دسترسی به این سفارش را ندارید.');
        }

        // Ensure the order is in 'unpaid' state
        if ($order->payment_status !== 'unpaid') {
            if ($order->payment_status === 'paid') {
                return redirect()->route('market.checkout.success', $order)->with('info', 'این سفارش قبلاً پرداخت شده است.');
            }
            return redirect()->route('market.checkout.failed', $order)->with('info', 'این سفارش قبلاً پردازش شده است.');
        }

        $method = $order->payment_method;
        $paymentStatus = \Modules\Market\Entities\MarketSetting::getValue("orders.status_{$method}_payment", 'unpaid');
        $deliveryStatus = \Modules\Market\Entities\MarketSetting::getValue("orders.status_{$method}_delivery", 'processing');

        switch ($method) {
            case 'pos':
            case 'transfer':
            case 'cod':
                // For offline payments, the order is already created.
                // We just need to show a success page with instructions.
                $order->update([
                    'payment_status' => $paymentStatus,
                    'delivery_status' => $deliveryStatus,
                ]);
                return redirect()->route('market.checkout.success', $order);

            default:
                try {
                    return $paymentService->redirectToGateway($order);
                } catch (\Throwable $e) {
                    \Log::error('Gateway Redirect failed for order #' . $order->id . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString());
                    
                    // Mark order as failed and release stock
                    $order->payment_status = 'failed';
                    $order->delivery_status = 'canceled';
                    $order->save();
                    
                    try {
                        (new \Modules\Market\App\Services\StockService())->releaseReservation($order);
                    } catch (\Throwable $stockEx) {
                        \Log::error('Failed to release stock for order #' . $order->id . ': ' . $stockEx->getMessage());
                    }

                    return redirect()->route('market.checkout.failed', $order)->with('error', 'خطا در هدایت به درگاه پرداخت: ' . $e->getMessage());
                }
        }
    }

    /**
     * Handle the payment gateway callback.
     *
     * @param Request $request
     * @param PaymentService $paymentService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request, PaymentService $paymentService)
    {
        $order = $paymentService->verifyPayment($request);

        if ($order) {
            // Payment was successful
            return redirect()->route('market.checkout.success', $order)->with('success', 'پرداخت شما با موفقیت انجام شد.');
        }

        // Payment failed or was canceled
        $orderId = $request->input('order_id') ?: $request->query('order_id');
        $order = Order::find($orderId);
        return redirect()->route('market.checkout.failed', $order)->with('error', 'پرداخت ناموفق بود یا توسط شما لغو شد.');
    }

    /**
     * Display the success page.
     *
     * @param Order $order
     * @return \Illuminate\Contracts\View\View
     */
    public function success(Order $order)
    {
        if ($order->client_id !== auth('client')->id()) {
            abort(403);
        }
        return view('market::web.checkout.success', compact('order'));
    }

    /**
     * Display the failed page.
     *
     * @param Order $order
     * @return \Illuminate\Contracts\View\View
     */
    public function failed(Order $order)
    {
         if ($order->client_id !== auth('client')->id()) {
            abort(403);
        }
        return view('market::web.checkout.failed', compact('order'));
    }
}
