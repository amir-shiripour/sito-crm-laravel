<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Services\PaymentService;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Start the payment process (Example endpoint to initiate a test payment)
     */
    public function request(Request $request)
    {
        // This is a dummy example. In a real scenario, you'd get the amount from an order/invoice.
        $amount = $request->input('amount', 10000); // Toman
        $gateway = $request->input('gateway', 'zarinpal');
        $description = $request->input('description', 'پرداخت تست');

        try {
            $paymentService = new PaymentService($gateway);

            $result = $paymentService->requestPayment(
                $amount,
                $description,
                auth()->user()->email ?? 'info@example.com',
                null, // mobile
                route('settings.payment.verify', ['gateway' => $gateway]) // callback
            );

            if ($result['success']) {
                // Store payment info in DB
                Payment::create([
                    'user_id' => auth()->id() ?? null,
                    'amount' => $amount,
                    'gateway' => $gateway,
                    'authority' => $result['authority'],
                    'status' => 'pending',
                    'description' => $description,
                ]);

                // Redirect to Zarinpal
                return redirect()->away($result['payment_url']);
            } else {
                Log::error('Payment request failed from gateway', ['result' => $result]);
                return redirect()->back()->with('error', 'خطا در ارتباط با درگاه پرداخت: ' . $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Payment request exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'خطا در سیستم پرداخت: ' . $e->getMessage());
        }
    }

    /**
     * Callback method called by the gateway after payment attempt
     */
    public function verify(Request $request, $gateway)
    {
        $authority = $request->query('Authority');
        $status = $request->query('Status');

        if (!$authority) {
            return redirect()->route('settings.index')->with('error', 'اطلاعات پرداخت معتبر نیست.');
        }

        // Find the pending payment
        $payment = Payment::where('authority', $authority)->where('status', 'pending')->first();

        if (!$payment) {
            return redirect()->route('settings.index')->with('error', 'تراکنش یافت نشد یا قبلاً بررسی شده است.');
        }

        if ($status === 'NOK') {
            $payment->update(['status' => 'failed']);
            return redirect()->route('settings.index')->with('error', 'پرداخت توسط کاربر لغو شد.');
        }

        try {
            $paymentService = new PaymentService($gateway);

            $dataToVerify = [
                'Authority' => $authority,
                'Status'    => $status,
                'Amount'    => $payment->amount, // Amount in Toman
            ];

            $result = $paymentService->verifyPayment($dataToVerify);

            if ($result['success']) {
                // Payment successful
                $payment->update([
                    'status' => 'success',
                    'ref_id' => $result['ref_id']
                ]);

                // Here you would typically trigger events (e.g., mark invoice as paid, send email)

                return redirect()->route('settings.index')->with('success', 'پرداخت با موفقیت انجام شد. کد پیگیری: ' . $result['ref_id']);
            } else {
                // Payment failed during verification
                $payment->update(['status' => 'failed']);
                Log::error('Payment verification failed from gateway', ['result' => $result]);
                return redirect()->route('settings.index')->with('error', 'خطا در تایید پرداخت: ' . $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Payment verify exception: ' . $e->getMessage());
            return redirect()->route('settings.index')->with('error', 'خطا در سیستم تایید پرداخت: ' . $e->getMessage());
        }
    }
}
