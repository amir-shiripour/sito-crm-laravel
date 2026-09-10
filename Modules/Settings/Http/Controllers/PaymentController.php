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
        $amount = $request->input('amount', 1000); // Toman
        $gateway = $request->input('gateway') ?: 'zarinpal';
        $description = $request->input('description', 'پرداخت تست');

        // Store origin URL to return to after payment callback
        $returnUrl = $request->input('return_url') ?: url()->previous();
        if ($returnUrl) {
            session(['payment_test_return_url' => $returnUrl]);
        }

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

                // Redirect to Gateway
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
        $authority = null;
        $dataToVerify = [];

        if ($gateway === 'zarinpal') {
            $authority = $request->query('Authority');
            $status = $request->query('Status');

            if (!$authority) {
                return $this->redirectBackToSource('error', 'اطلاعات پرداخت معتبر نیست.');
            }

            if ($status === 'NOK') {
                // Find payment and mark as failed without verification
                $payment = Payment::where('authority', $authority)->where('status', 'pending')->first();
                if ($payment) {
                    $payment->update(['status' => 'failed']);
                }
                return $this->redirectBackToSource('error', 'پرداخت توسط کاربر لغو شد.');
            }

            $dataToVerify = [
                'Authority' => $authority,
                'Status'    => $status,
            ];

        } elseif ($gateway === 'zibal') {
            $authority = $request->input('trackId'); // Zibal uses trackId
            $success = $request->input('success');

            if (!$authority) {
                return $this->redirectBackToSource('error', 'اطلاعات پرداخت معتبر نیست.');
            }

            if ($success != 1) {
                // Find payment and mark as failed without verification
                $payment = Payment::where('authority', $authority)->where('status', 'pending')->first();
                if ($payment) {
                    $payment->update(['status' => 'failed']);
                }
                return $this->redirectBackToSource('error', 'پرداخت توسط کاربر لغو شد یا ناموفق بود.');
            }

            $dataToVerify = $request->all(); // Pass all query/post params
        } elseif ($gateway === 'behpardakht') {
            $authority = $request->input('RefId');
            $resCode   = (string) $request->input('ResCode', '-1');

            if (!$authority) {
                return $this->redirectBackToSource('error', 'شناسه مرجع پرداخت (RefId) در کالبک بانک یافت نشد.');
            }

            if ($resCode !== '0' && $resCode !== '00') {
                $payment = Payment::where('authority', $authority)->where('status', 'pending')->first();
                if ($payment) {
                    $payment->update(['status' => 'failed']);
                }
                return $this->redirectBackToSource('error', 'پرداخت در درگاه ملت با خطا مواجه شد یا توسط کاربر لغو شد. (کد: ' . $resCode . ')');
            }

            $dataToVerify = $request->all();
        } elseif ($gateway === 'sep' || $gateway === 'saman') {
            $token   = $request->input('Token');
            $refNum  = $request->input('RefNum');
            $resNum  = $request->input('ResNum');
            $state   = strtoupper((string) $request->input('State', ''));
            $status  = (int) $request->input('Status', -1);

            $authority = $token ?: ($resNum ?: $refNum);

            if (!$authority && !$refNum) {
                return $this->redirectBackToSource('error', 'اطلاعات کالبک درگاه پرداخت سامان کیش (سپ) یافت نشد.');
            }

            if ($state !== 'OK' || $status !== 2 || empty($refNum)) {
                $payment = Payment::where(function($q) use ($token, $resNum, $refNum) {
                    if ($token) $q->where('authority', $token);
                    if ($resNum) $q->orWhere('authority', $resNum);
                    if ($refNum) $q->orWhere('authority', $refNum);
                })->where('status', 'pending')->first();

                if ($payment) {
                    $payment->update(['status' => 'failed']);
                }

                $statusMsg = (new PaymentService('sep'))->getSepStatusMessage($status, $state);
                return $this->redirectBackToSource('error', 'پرداخت در درگاه سامان کیش با خطا مواجه شد یا توسط کاربر لغو شد: ' . $statusMsg);
            }

            $dataToVerify = $request->all();
        } else {
            return $this->redirectBackToSource('error', 'درگاه پرداخت ناشناخته است.');
        }

        // Find the pending payment using the authority/trackId/Token
        $payment = Payment::where(function($q) use ($authority, $request, $gateway) {
            if ($authority) {
                $q->where('authority', $authority);
            }
            if ($gateway === 'sep' || $gateway === 'saman') {
                if ($t = $request->input('Token')) $q->orWhere('authority', $t);
                if ($r = $request->input('ResNum')) $q->orWhere('authority', $r);
                if ($ref = $request->input('RefNum')) $q->orWhere('authority', $ref);
            }
        })->where('status', 'pending')->first();

        if (!$payment) {
            return $this->redirectBackToSource('error', 'تراکنش یافت نشد یا قبلاً بررسی شده است.');
        }

        // Add amount to data for verification (required by gateways in our service)
        $dataToVerify['Amount'] = $payment->amount;

        try {
            $paymentService = new PaymentService($gateway);
            $result = $paymentService->verifyPayment($dataToVerify);

            if ($result['success']) {
                // Payment successful
                $payment->update([
                    'status' => 'success',
                    'ref_id' => $result['ref_id']
                ]);

                return $this->redirectBackToSource('success', 'پرداخت با موفقیت انجام شد. کد پیگیری: ' . $result['ref_id']);
            } else {
                // Payment failed during verification
                $payment->update(['status' => 'failed']);
                Log::error('Payment verification failed from gateway', ['result' => $result]);
                return $this->redirectBackToSource('error', 'خطا در تایید پرداخت: ' . ($result['message'] ?? 'خطای ناشناخته'));
            }
        } catch (\Exception $e) {
            Log::error('Payment verify exception: ' . $e->getMessage());
            return $this->redirectBackToSource('error', 'خطا در سیستم تایید پرداخت: ' . $e->getMessage());
        }
    }

    /**
     * Intermediate redirect page to auto-submit POST form to Behpardakht Mellat.
     */
    public function redirectBehpardakht(Request $request)
    {
        $refId = $request->query('ref_id') ?: $request->input('ref_id');
        $mobile = $request->query('mobile') ?: $request->input('mobile');

        if (!$refId) {
            return redirect()->route('settings.index')->with('error', 'شناسه مرجع پرداخت (RefId) نامعتبر است.');
        }

        return view('settings::partials.behpardakht_redirect', [
            'refId'  => $refId,
            'mobile' => $mobile,
        ]);
    }

    /**
     * Intermediate redirect page to auto-submit POST form to Saman Electronic Payment (SEP).
     */
    public function redirectSep(Request $request)
    {
        $token = $request->query('token') ?: $request->input('token');

        if (!$token) {
            return redirect()->route('settings.index')->with('error', 'توکن درگاه سامان کیش (سپ) نامعتبر است.');
        }

        return view('settings::partials.sep_redirect', [
            'token' => $token,
        ]);
    }

    /**
     * Helper to redirect back to the initiating page (e.g. /user/settings/payment or /settings#payment)
     */
    protected function redirectBackToSource(string $type, string $message)
    {
        $returnUrl = session('payment_test_return_url');
        session()->forget('payment_test_return_url');

        if ($returnUrl && filter_var($returnUrl, FILTER_VALIDATE_URL)) {
            return redirect()->to($returnUrl)->with($type, $message);
        }

        return redirect()->route('settings.index')->with($type, $message);
    }
}
