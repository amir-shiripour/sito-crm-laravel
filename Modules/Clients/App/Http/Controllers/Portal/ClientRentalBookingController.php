<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Properties\Entities\PropertyRentalBooking;
use Modules\Properties\Services\RentalBookingService;
use Modules\Properties\Entities\PropertySetting;
use Modules\Settings\Entities\Setting;
use App\Services\PaymentService as GlobalPaymentService;

class ClientRentalBookingController extends Controller
{
    protected RentalBookingService $bookingService;

    public function __construct(RentalBookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * لیست رزروهای اقامتگاهی کلاینت
     */
    public function index(Request $request)
    {
        $client = auth('client')->user();
        if (!$client) {
            abort(403);
        }

        $query = PropertyRentalBooking::with(['property.primaryImage', 'host'])
            ->where('client_id', $client->id);

        $status = $request->get('status', 'all');
        if ($status && $status !== 'all') {
            if ($status === 'active') {
                $query->whereIn('status', [
                    PropertyRentalBooking::STATUS_PENDING_HOST,
                    PropertyRentalBooking::STATUS_AWAITING_PAYMENT,
                    PropertyRentalBooking::STATUS_CONFIRMED,
                    PropertyRentalBooking::STATUS_CHECKED_IN,
                ]);
            } elseif ($status === 'history') {
                $query->whereIn('status', [
                    PropertyRentalBooking::STATUS_COMPLETED,
                    PropertyRentalBooking::STATUS_CANCELLED_BY_GUEST,
                    PropertyRentalBooking::STATUS_CANCELLED_BY_HOST,
                    PropertyRentalBooking::STATUS_CANCELLED_BY_ADMIN,
                    PropertyRentalBooking::STATUS_REJECTED,
                    PropertyRentalBooking::STATUS_EXPIRED,
                ]);
            } else {
                $query->where('status', $status);
            }
        }

        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                  ->orWhereHas('property', function($pq) use ($search) {
                      $pq->where('title', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $query->orderByDesc('id')->paginate(10)->withQueryString();

        // شمارش‌ها برای تب‌های فیلتر
        $counts = [
            'all' => PropertyRentalBooking::where('client_id', $client->id)->count(),
            'active' => PropertyRentalBooking::where('client_id', $client->id)
                ->whereIn('status', [
                    PropertyRentalBooking::STATUS_PENDING_HOST,
                    PropertyRentalBooking::STATUS_AWAITING_PAYMENT,
                    PropertyRentalBooking::STATUS_CONFIRMED,
                    PropertyRentalBooking::STATUS_CHECKED_IN,
                ])->count(),
            'pending_host' => PropertyRentalBooking::where('client_id', $client->id)
                ->where('status', PropertyRentalBooking::STATUS_PENDING_HOST)->count(),
            'awaiting_payment' => PropertyRentalBooking::where('client_id', $client->id)
                ->where('status', PropertyRentalBooking::STATUS_AWAITING_PAYMENT)->count(),
            'confirmed' => PropertyRentalBooking::where('client_id', $client->id)
                ->where('status', PropertyRentalBooking::STATUS_CONFIRMED)->count(),
            'history' => PropertyRentalBooking::where('client_id', $client->id)
                ->whereIn('status', [
                    PropertyRentalBooking::STATUS_COMPLETED,
                    PropertyRentalBooking::STATUS_CANCELLED_BY_GUEST,
                    PropertyRentalBooking::STATUS_CANCELLED_BY_HOST,
                    PropertyRentalBooking::STATUS_CANCELLED_BY_ADMIN,
                    PropertyRentalBooking::STATUS_REJECTED,
                    PropertyRentalBooking::STATUS_EXPIRED,
                ])->count(),
        ];

        return view('clients::portal.rental-bookings.index', compact('bookings', 'counts', 'status'));
    }

    /**
     * مشاهده جزئیات و سند تأییدیه رزرو
     */
    public function show(PropertyRentalBooking $booking)
    {
        $client = auth('client')->user();
        if ($booking->client_id !== $client->id) {
            abort(403, 'شما به این رزرو دسترسی ندارید.');
        }

        $booking->load(['property.primaryImage', 'property.rentalConfig', 'host']);

        $currency = PropertySetting::get('rental_currency', 'IRT');
        $currencyLabel = in_array(strtolower($currency), ['irr', 'rial', 'ریال']) ? 'ریال' : 'تومان';

        // بارگذاری تنظیمات روش‌های پرداخت فعال در سیستم
        $settingsMap = Schema::hasTable('settings')
            ? Setting::query()->pluck('value', 'key')->toArray()
            : [];

        $activeRaw = $settingsMap['active_payment_methods'] ?? '[]';
        $activeMethods = is_string($activeRaw) ? json_decode($activeRaw, true) : (array) $activeRaw;
        if (empty($activeMethods) || !is_array($activeMethods)) {
            $activeMethods = ['online', 'transfer'];
        }

        $onlineGateways = [];
        if (($settingsMap['zarinpal_status'] ?? '') === 'active') {
            $onlineGateways[] = ['id' => 'zarinpal', 'label' => 'درگاه پرداخت زرین‌پال'];
        }
        if (($settingsMap['zibal_status'] ?? '') === 'active') {
            $onlineGateways[] = ['id' => 'zibal', 'label' => 'درگاه پرداخت زیبال'];
        }
        if (($settingsMap['behpardakht_status'] ?? '') === 'active') {
            $onlineGateways[] = ['id' => 'behpardakht', 'label' => 'درگاه بهپرداخت ملت'];
        }
        if (($settingsMap['sep_status'] ?? '') === 'active') {
            $onlineGateways[] = ['id' => 'sep', 'label' => 'درگاه سامان کیش (سپ)'];
        }

        // اگر هیچ درگاهی با وضعیت اکتیو نبود ولی دیفالت ست شده بود
        if (empty($onlineGateways)) {
            $defaultGw = $settingsMap['default_payment_gateway'] ?? 'zarinpal';
            $onlineGateways[] = ['id' => $defaultGw, 'label' => 'درگاه پرداخت آنلاین (' . $defaultGw . ')'];
        }

        $rawAccounts = is_string($settingsMap['bank_transfer_accounts'] ?? null)
            ? (json_decode($settingsMap['bank_transfer_accounts'], true) ?: [])
            : (is_array($settingsMap['bank_transfer_accounts'] ?? null) ? $settingsMap['bank_transfer_accounts'] : []);

        $activeBankAccounts = array_filter($rawAccounts, function($acc) {
            if (!isset($acc['is_active'])) return true;
            return filter_var($acc['is_active'], FILTER_VALIDATE_BOOLEAN);
        });

        $bankAccounts = array_values(array_map(function($acc, $index) {
            return [
                'id'             => $acc['id'] ?? ($acc['bank_name'] ?? ('acc_' . $index)),
                'bank_name'      => $acc['bank_name'] ?? 'بانک',
                'owner_name'     => $acc['owner_name'] ?? ($acc['owner'] ?? ''),
                'card_number'    => !empty($acc['card_number']) ? preg_replace('/[^0-9]/', '', $acc['card_number']) : '',
                'account_number' => $acc['account_number'] ?? '',
                'iban'           => $acc['iban'] ?? '',
            ];
        }, $activeBankAccounts, array_keys($activeBankAccounts)));

        $availablePaymentMethods = [];
        if (in_array('online', $activeMethods) && !empty($onlineGateways)) {
            $availablePaymentMethods['online'] = 'پرداخت آنلاین بانکی (شتاب)';
        }
        if (in_array('transfer', $activeMethods) && ($settingsMap['bank_transfer_status'] ?? '') === 'active' && !empty($bankAccounts)) {
            $availablePaymentMethods['transfer'] = 'کارت به کارت / انتقال بانکی';
        }

        $paymentSubItems = [
            'online' => $onlineGateways,
            'transfer' => array_values(array_map(fn($a) => [
                'id' => $a['id'],
                'label' => ($a['bank_name'] ?: 'بانک') . ($a['owner_name'] ? ' - ' . $a['owner_name'] : '') . ($a['card_number'] ? ' (' . $a['card_number'] . ')' : '')
            ], $bankAccounts)),
        ];

        // قوانین کنسلی اقامتگاه
        $rentalConfig = $booking->property?->rentalConfig;
        $cancellationPolicy = $rentalConfig?->cancellation_policy ?? 'moderate';
        $cancellationRulesText = match($cancellationPolicy) {
            'flexible' => 'کنسلی تا ۴۸ ساعت قبل از تاریخ ورود رایگان است و پس از آن مبلغ شب اول کسر می‌گردد.',
            'strict'   => 'کنسلی تا ۵ روز قبل از ورود با کسر ۲۰٪ و پس از آن با کسر ۵۰٪ مبلغ انجام خواهد شد.',
            default    => 'کنسلی تا ۷۲ ساعت قبل از موعد ورود بدون جریمه بوده و کمتر از آن با کسر هزینه شب اول امکان‌پذیر است.',
        };

        return view('clients::portal.rental-bookings.show', compact(
            'booking',
            'currency',
            'currencyLabel',
            'availablePaymentMethods',
            'paymentSubItems',
            'onlineGateways',
            'bankAccounts',
            'cancellationRulesText'
        ));
    }

    /**
     * چاپ سند و تأییدیه رسمی رزرو اقامتگاه
     */
    public function print(PropertyRentalBooking $booking)
    {
        $client = auth('client')->user();
        if ($booking->client_id !== $client->id) {
            abort(403);
        }

        $booking->load(['property.rentalConfig', 'host']);

        $currency = PropertySetting::get('rental_currency', 'IRT');
        $currencyLabel = in_array(strtolower($currency), ['irr', 'rial', 'ریال']) ? 'ریال' : 'تومان';

        return view('clients::portal.rental-bookings.print', compact('booking', 'currencyLabel'));
    }

    /**
     * پرداخت وجه رزرو اقامتگاه (آنلاین یا ثبت فیش کارت‌به‌کارت)
     */
    public function pay(Request $request, PropertyRentalBooking $booking)
    {
        $client = auth('client')->user();
        if ($booking->client_id !== $client->id) {
            return response()->json(['success' => false, 'message' => 'دسترسی غیرمجاز'], 403);
        }

        if (!in_array($booking->status, [PropertyRentalBooking::STATUS_AWAITING_PAYMENT, PropertyRentalBooking::STATUS_PENDING_HOST])) {
            return response()->json(['success' => false, 'message' => 'این رزرو در وضعیت قابل پرداخت نمی‌باشد.'], 422);
        }

        if ($booking->payment_status === PropertyRentalBooking::PAYMENT_PAID) {
            return response()->json(['success' => false, 'message' => 'این رزرو قبلاً پرداخت و تسویه شده است.'], 422);
        }

        $paymentMethod = $request->input('payment_method', 'online');

        // ۱. پرداخت از طریق درگاه پرداخت آنلاین شاپرک
        if ($paymentMethod === 'online') {
            $settingsMap = Schema::hasTable('settings')
                ? Setting::query()->pluck('value', 'key')->toArray()
                : [];

            $gateway = $request->input('gateway') ?: $request->input('sub_item');
            if (empty($gateway)) {
                $gateway = $settingsMap['default_payment_gateway'] ?? 'zarinpal';
            }

            $currency = PropertySetting::get('rental_currency', 'IRT');
            $rawAmount = (float) $booking->total_price;
            $amountInRials = in_array(strtolower($currency), ['toman', 'irt', 'تومان'])
                ? (int) ($rawAmount * 10)
                : (int) $rawAmount;

            try {
                $globalPaymentService = new GlobalPaymentService($gateway);
                $description = "پرداخت رزرو اقامتگاه " . ($booking->property?->title ?? 'اقامتگاه') . " (کد رزرو: {$booking->booking_code})";
                $email = $client->email;
                $mobile = $booking->guest_phone ?: ($client->mobile ?? $client->phone);

                $callbackUrl = route('client.rental-bookings.verify', [
                    'booking' => $booking->id,
                    'gateway' => $gateway,
                ]);

                $result = $globalPaymentService->requestPaymentInRials(
                    $amountInRials,
                    $description,
                    $email,
                    $mobile,
                    $callbackUrl
                );

                if (!empty($result['success']) && !empty($result['payment_url'])) {
                    // ثبت سابقه درخواست پرداخت در متا رزرو
                    $meta = $booking->meta ?? [];
                    $meta['pending_online_payment'] = [
                        'gateway'         => $gateway,
                        'authority'       => $result['authority'] ?? null,
                        'amount_in_rials' => $amountInRials,
                        'initiated_at'    => now()->toDateTimeString(),
                    ];
                    $booking->update(['meta' => $meta]);

                    if ($request->wantsJson()) {
                        return response()->json([
                            'success'      => true,
                            'payment_url'  => $result['payment_url'],
                            'redirect_url' => $result['payment_url'],
                        ]);
                    }

                    return redirect()->away($result['payment_url']);
                }

                $errMsg = $result['message'] ?? 'خطا در برقراری ارتباط با درگاه پرداخت.';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $errMsg], 422);
                }
                return back()->with('error', $errMsg);

            } catch (\Throwable $e) {
                Log::error('[ClientRentalBookingController] Online pay error: ' . $e->getMessage());
                $errMsg = 'خطا در ارسال به درگاه بانکی: ' . $e->getMessage();
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $errMsg], 500);
                }
                return back()->with('error', $errMsg);
            }
        }

        // ۲. پرداخت از طریق کارت به کارت / انتقال بانکی (آپلود فیش و شماره پیگیری)
        if ($paymentMethod === 'transfer') {
            $request->validate([
                'tracking_code' => 'required|string|max:100',
                'bank_id'       => 'nullable|string|max:100',
                'payment_date'  => 'nullable|string|max:50',
                'receipt_file'  => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            ], [
                'tracking_code.required' => 'لطفاً شماره پیگیری یا ارجاع واریز را وارد نمایید.',
            ]);

            $receiptPath = null;
            if ($request->hasFile('receipt_file')) {
                try {
                    $file = $request->file('receipt_file');
                    $filename = 'receipt_' . $booking->booking_code . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $receiptPath = $file->storeAs('receipts/rentals', $filename, 'public');
                } catch (\Throwable $e) {
                    Log::error('[ClientRentalBookingController] Receipt upload error: ' . $e->getMessage());
                }
            }

            $meta = $booking->meta ?? [];
            $meta['bank_receipt'] = [
                'tracking_code' => $request->input('tracking_code'),
                'bank_id'       => $request->input('bank_id'),
                'payment_date'  => $request->input('payment_date', jdate()->format('Y/m/d')),
                'receipt_path'  => $receiptPath,
                'submitted_at'  => now()->toDateTimeString(),
            ];

            $booking->update([
                'payment_method' => 'transfer',
                'notes'          => ($booking->notes ? $booking->notes . ' | ' : '') . 'ثبت فیش واریزی مهمان با شماره پیگیری: ' . $request->input('tracking_code'),
                'meta'           => $meta,
            ]);

            $msg = 'اطلاعات فیش واریزی با موفقیت ثبت شد و پس از بررسی توسط پشتیبانی تأیید خواهد شد.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success'      => true,
                    'message'      => $msg,
                    'redirect_url' => route('client.rental-bookings.show', $booking->id),
                ]);
            }

            return redirect()->route('client.rental-bookings.show', $booking->id)
                ->with('success', $msg);
        }

        return response()->json(['success' => false, 'message' => 'روش پرداخت نامعتبر است.'], 422);
    }

    /**
     * اعتبارسنجی کالبک عمومی درگاه پرداخت آنلاین شاپرک
     */
    public function verifyOnlinePayment(Request $request, PropertyRentalBooking $booking, $gateway = null)
    {
        $gateway = $gateway ?: $request->query('gateway') ?: $request->input('gateway');

        // اطمینان از لاگین بودن کلاینت هنگام بازگشت از بانک
        if (!auth('client')->check() && $booking->client) {
            try {
                auth('client')->login($booking->client);
            } catch (\Throwable $e) {
                Log::warning('[ClientRentalBookingController] Auto-login client error: ' . $e->getMessage());
            }
        }

        // استخراج توکن یا شناسه بازگشتی درگاه
        $authority = $request->input('Authority')
            ?: ($request->input('trackId')
            ?: ($request->input('RefId')
            ?: ($request->input('Token')
            ?: ($request->input('ResNum')
            ?: $request->input('RefNum')))));

        if (!$gateway) {
            $pendingPayment = data_get($booking->meta, 'pending_online_payment');
            $gateway = $pendingPayment['gateway'] ?? Setting::where('key', 'default_payment_gateway')->value('value') ?? 'zarinpal';
        }

        // بررسی لغو تراکنش توسط کاربر در درگاه
        $status = 'OK';
        if ($gateway === 'zibal') {
            $status = ((string)($request->input('success') ?? '')) === '1' ? 'OK' : 'NOK';
        } elseif ($gateway === 'behpardakht') {
            $resCode = (string)($request->input('ResCode') ?? '-1');
            $status = ($resCode === '0' || $resCode === '00') ? 'OK' : 'NOK';
        } elseif ($gateway === 'sep' || $gateway === 'saman') {
            $sepState = strtoupper((string)($request->input('State') ?? ''));
            $sepStatus = (int)($request->input('Status') ?? -1);
            $status = ($sepState === 'OK' && $sepStatus === 2) ? 'OK' : 'NOK';
        } else {
            $status = strtoupper((string)($request->input('Status') ?? ''));
        }

        if ($status === 'NOK' || $status === 'CANCELED') {
            return redirect()
                ->route('client.rental-bookings.show', $booking->id)
                ->with('error', 'پرداخت آنلاین توسط کاربر لغو گردید یا با خطا مواجه شد.');
        }

        try {
            $globalPaymentService = new GlobalPaymentService($gateway);

            $currency = PropertySetting::get('rental_currency', 'IRT');
            $rawAmount = (float) $booking->total_price;
            $amountInRials = in_array(strtolower($currency), ['toman', 'irt', 'تومان'])
                ? (int) ($rawAmount * 10)
                : (int) $rawAmount;

            $dataToVerify = $request->all();
            if ($gateway === 'zibal') {
                $dataToVerify['trackId'] = $authority;
                $dataToVerify['Amount']  = $amountInRials;
            } elseif ($gateway === 'behpardakht') {
                $dataToVerify['RefId']   = $authority;
                $dataToVerify['Amount']  = $amountInRials;
            } elseif ($gateway === 'sep' || $gateway === 'saman') {
                $dataToVerify['Token']   = $authority;
                $dataToVerify['RefNum']  = $request->input('RefNum');
                $dataToVerify['Amount']  = $amountInRials;
            } else {
                $dataToVerify['Authority'] = $authority;
                $dataToVerify['Amount']    = $amountInRials;
            }

            $verifyResult = $globalPaymentService->verifyPayment($dataToVerify);

            if ($verifyResult && ($verifyResult['success'] ?? false)) {
                $refId = $verifyResult['ref_id'] ?? $verifyResult['reference_id'] ?? $authority;

                // تایید قطعی رزرو از طریق سرویس مرکزی
                $this->bookingService->confirmBooking($booking, [
                    'payment_method' => $gateway,
                    'transaction_id' => $refId,
                    'paid_amount'    => $booking->total_price,
                    'actor_type'     => 'client',
                    'actor_id'       => $booking->client_id,
                    'note'           => "پرداخت آنلاین موفق از طریق درگاه {$gateway} | شماره پیگیری: {$refId}",
                ]);

                return redirect()
                    ->route('client.rental-bookings.show', $booking->id)
                    ->with('success', "پرداخت با موفقیت انجام شد و رزرو شما قطعی گردید. کد رهگیری پرداخت: {$refId}");
            }

            $errMsg = $verifyResult['message'] ?? 'تراکنش توسط بانک تأیید نشد.';
            return redirect()
                ->route('client.rental-bookings.show', $booking->id)
                ->with('error', 'خطا در تایید تراکنش: ' . $errMsg);

        } catch (\Throwable $e) {
            Log::error('[ClientRentalBookingController] Online verify error: ' . $e->getMessage());
            return redirect()
                ->route('client.rental-bookings.show', $booking->id)
                ->with('error', 'خطای سیستمی در تایید پرداخت: ' . $e->getMessage());
        }
    }

    /**
     * لغو رزرو توسط مهمان
     */
    public function cancel(Request $request, PropertyRentalBooking $booking)
    {
        $client = auth('client')->user();
        if ($booking->client_id !== $client->id) {
            return response()->json(['success' => false, 'message' => 'دسترسی غیرمجاز'], 403);
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:500'
        ], [
            'cancellation_reason.required' => 'لطفاً علت انصراف و لغو رزرو را وارد فرمایید.',
        ]);

        try {
            $this->bookingService->cancelBooking(
                $booking,
                $request->input('cancellation_reason'),
                'guest',
                $client->id
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'رزرو شما با موفقیت لغو شد.'
                ]);
            }

            return redirect()->route('client.rental-bookings.show', $booking->id)
                ->with('success', 'رزرو شما با موفقیت لغو شد.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
