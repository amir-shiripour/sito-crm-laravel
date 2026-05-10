<?php

namespace Modules\Booking\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Entities\BookingPayment;
use Modules\Booking\Services\AppointmentService;
use Modules\Booking\Services\BookingEngine;
use Modules\Booking\Services\PaymentService;
use Modules\Clients\Entities\ClientSetting;
use Modules\Clients\Entities\Client;
use Modules\Sms\Entities\SmsOtp;
use Modules\Sms\Services\SmsManager;
use Modules\Sms\Entities\SmsGatewaySetting;
use Modules\Sms\Entities\SmsMessage;
use Morilog\Jalali\CalendarUtils;
use Carbon\Carbon;

class OnlineBookingController extends Controller
{
    private function applyTax($price, $settings)
    {
        if (!$settings->tax_enabled || empty($price)) {
            return $price;
        }
        $amount = (float) $settings->tax_amount;
        if ($settings->tax_type === 'PERCENT') {
            return $price + ($price * $amount / 100);
        }
        return $price + $amount;
    }

    public function index(BookingEngine $engine)
    {
        $settings = BookingSetting::current();
        $flow = $settings->user_appointment_flow ?? 'SERVICE_FIRST';

        // فقط سرویس‌های فعال را بگیر
        $services = BookingService::query()
            ->where('status', BookingService::STATUS_ACTIVE)
            ->with(['serviceProviders' => function ($query) {
                $query->where('is_active', true)->with('provider');
            }])
            ->orderBy('name')
            ->get();

        // فیلتر کردن سرویس‌هایی که حداقل یک provider فعال دارند و امکان رزرو آنلاین دارند
        $availableServices = $services->filter(function ($service) use ($engine, $settings) {
            // اگر رزرو آنلاین در سطح global غیرفعال است
            if (!$settings->global_online_booking_enabled) {
                return false;
            }

            // بررسی اینکه آیا حداقل یک provider فعال دارد که امکان رزرو آنلاین دارد
            foreach ($service->serviceProviders as $sp) {
                if ($sp->is_active && $engine->isOnlineBookingEnabled($service->id, $sp->provider_user_id)) {
                    return true;
                }
            }

            return false;
        });

        // محاسبه و اعمال ارزش افزوده (مالیات) روی تمام سرویس‌ها
        $availableServices->transform(function ($service) use ($settings) {
            $service->final_price = $this->applyTax($service->base_price, $settings);
            return $service;
        });

        // اگر جریان روی "ارائه‌دهنده اول" بود، لیست پزشکان را استخراج و نمایش می‌دهیم
        if ($flow === 'PROVIDER_FIRST') {
            $providers = collect();
            foreach ($availableServices as $service) {
                foreach ($service->serviceProviders as $sp) {
                    if ($sp->is_active && $engine->isOnlineBookingEnabled($service->id, $sp->provider_user_id)) {
                        $prov = $sp->provider;
                        if ($prov && !$providers->contains('id', $prov->id)) {
                            // ذخیره حداقل قیمت سرویس‌ها برای این پزشک (جهت نمایش شروع قیمت از...)
                            $prov->min_price = $service->final_price;
                            $providers->push($prov);
                        } else if ($prov) {
                            $existingProv = $providers->firstWhere('id', $prov->id);
                            $existingProv->min_price = min($existingProv->min_price, $service->final_price);
                        }
                    }
                }
            }
            return view('booking::web.index', [
                'items' => $providers,
                'flow' => $flow,
                'settings' => $settings
            ]);
        }

        return view('booking::web.index', [
            'items' => $availableServices,
            'flow' => $flow,
            'settings' => $settings
        ]);
    }

    public function service(BookingService $service)
    {
        $service->load(['serviceProviders.provider', 'appointmentForm']);
        $settings = BookingSetting::current();

        // اعمال مالیات برای نمایش نهایی
        $service->final_price = $this->applyTax($service->base_price, $settings);

        $now = Carbon::now();
        $jDate = CalendarUtils::toJalali($now->year, $now->month, $now->day);
        $currentJalali = [
            'year' => $jDate[0],
            'month' => $jDate[1],
            'day' => $jDate[2],
        ];

        $clientMode = ClientSetting::getValue('auth.mode', 'password');
        $defaultLogin = ClientSetting::getValue('auth.default', 'password');

        $otpTtl = (int) ClientSetting::getValue('auth.otp_ttl', 5);
        $otpResendInterval = (int) ClientSetting::getValue('auth.otp_resend_interval', 60);

        return view('booking::web.service', compact('service', 'settings', 'currentJalali', 'otpTtl', 'otpResendInterval', 'clientMode', 'defaultLogin'));
    }

    public function provider($providerId, BookingEngine $engine)
    {
        $settings = BookingSetting::current();

        $services = BookingService::query()
            ->where('status', BookingService::STATUS_ACTIVE)
            ->whereHas('serviceProviders', function ($query) use ($providerId) {
                $query->where('is_active', true)->where('provider_user_id', $providerId);
            })
            ->with(['serviceProviders' => function($q) use ($providerId) {
                $q->where('provider_user_id', $providerId)->with('provider');
            }, 'appointmentForm'])
            ->get();

        $availableServices = $services->filter(function ($service) use ($engine, $providerId, $settings) {
            return $settings->global_online_booking_enabled && $engine->isOnlineBookingEnabled($service->id, $providerId);
        });

        if ($availableServices->isEmpty()) {
            return redirect()->route('booking.public.index')->with('error', 'هیچ سرویس فعالی برای این ارائه‌دهنده یافت نشد.');
        }

        $provider = $availableServices->first()->serviceProviders->first()->provider;

        $availableServices->transform(function ($service) use ($settings) {
            $service->final_price = $this->applyTax($service->base_price, $settings);
            return $service;
        });

        $now = Carbon::now();
        $jDate = CalendarUtils::toJalali($now->year, $now->month, $now->day);
        $currentJalali = [
            'year' => $jDate[0],
            'month' => $jDate[1],
            'day' => $jDate[2],
        ];

        $clientMode = ClientSetting::getValue('auth.mode', 'password');
        $defaultLogin = ClientSetting::getValue('auth.default', 'password');
        $otpTtl = (int) ClientSetting::getValue('auth.otp_ttl', 5);
        $otpResendInterval = (int) ClientSetting::getValue('auth.otp_resend_interval', 60);

        return view('booking::web.provider', compact('provider', 'providerId', 'availableServices', 'settings', 'currentJalali', 'otpTtl', 'otpResendInterval', 'clientMode', 'defaultLogin'));
    }

    public function sendBookingOtp(Request $request, SmsManager $sms)
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'full_name' => ['required', 'string'],
        ]);

        $phone = $data['phone'];
        $fullName = $data['full_name'];

        // [Architecture Note]: Logic related to find/create client and managing OTP limits
        // really belongs in an AuthService or SmsManager, not the Controller.
        // Find or create client
        $client = Client::firstOrCreate(
            ['phone' => $phone],
            ['username' => $phone, 'full_name' => $fullName]
        );

        $otpLength         = (int) ClientSetting::getValue('auth.otp_length', 5);
        $otpTtl            = (int) ClientSetting::getValue('auth.otp_ttl', 5);
        $otpResendInterval = (int) ClientSetting::getValue('auth.otp_resend_interval', 60);
        $otpMaxRequests    = (int) ClientSetting::getValue('auth.otp_max_requests', 3);

        $otpLength = max(3, min(10, $otpLength));
        $otpTtl = max(1, min(60, $otpTtl));
        $otpResendInterval = max(10, min(600, $otpResendInterval));
        $otpMaxRequests = max(1, min(10, $otpMaxRequests));

        $context = 'booking_client';

        // 1) محدودیت ارسال مجدد (cooldown)
        $last = SmsOtp::query()
            ->where('phone', $phone)
            ->where('context', $context)
            ->latest()
            ->first();

        if ($last && $last->created_at && now()->diffInSeconds($last->created_at) < $otpResendInterval) {
            $remain = $otpResendInterval - now()->diffInSeconds($last->created_at);
            return response()->json([
                'success' => false,
                'message' => "برای ارسال مجدد، {$remain} ثانیه صبر کنید.",
                'resend_in' => $remain,
            ], 429);
        }

        // 2) محدودیت تعداد درخواست‌ها (در یک بازه کوتاه)
        $windowMinutes = max(5, $otpTtl);
        $recentCount = SmsOtp::query()
            ->where('phone', $phone)
            ->where('context', $context)
            ->where('created_at', '>=', now()->subMinutes($windowMinutes))
            ->count();

        if ($recentCount >= $otpMaxRequests) {
            return response()->json([
                'success' => false,
                'message' => 'تعداد درخواست‌های ارسال کد بیش از حد مجاز است. کمی بعد دوباره تلاش کنید.',
            ], 429);
        }

        // تولید کد
        $code = (string) random_int(10 ** ($otpLength - 1), (10 ** $otpLength) - 1);

        // پترن OTP کلاینت از تنظیمات SMS (آخرین رکورد)
        $patternId = null;
        if (class_exists(SmsGatewaySetting::class)) {
            $globalSetting = SmsGatewaySetting::query()->orderByDesc('id')->first();
            $patternId = data_get($globalSetting, 'config.client_otp_pattern');
        }

        $options = [
            'type'        => SmsMessage::TYPE_OTP,
            'related_type'=> 'CLIENT',
            'related_id'  => $client->id,
            'meta'        => [
                'context' => $context,
                'otp'     => $code,
            ],
        ];

        // اگر پترن ست شده → ارسال پترنی (ReplaceToken = [code])
        if (!empty($patternId)) {
            $sms->sendPattern($phone, (string) $patternId, [$code], $options);
        } else {
            // بدون پترن → متن ساده
            $sms->sendText($phone, "کد ورود شما: {$code}", $options);
        }

        SmsOtp::create([
            'phone'      => $phone,
            'code'       => $code,
            'context'    => $context,
            'client_id'  => $client->id,
            'expires_at' => now()->addMinutes($otpTtl),
            'meta'       => [
                'username' => $client->username,
            ],
        ]);

        return response()->json([
            'success' => true,
            'expires_in' => $otpTtl * 60,
            'resend_in'  => $otpResendInterval,
            'message' => 'کد تایید ارسال شد.',
        ]);
    }

    public function calendar(Request $request, BookingService $service, BookingEngine $engine)
    {
        $settings = BookingSetting::current();
        if (! $settings->global_online_booking_enabled) {
            return response()->json(['data' => []], 200);
        }

        $data = $request->validate([
            'provider_user_id' => ['required', 'integer'],
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $providerId = (int) $data['provider_user_id'];

        // اگر آنلاین بوکینگ برای این سرویس/ارائه‌دهنده فعال نیست، کل ماه رو خالی برگردون
        if (! $engine->isOnlineBookingEnabled($service->id, $providerId)) {
            return response()->json(['data' => []], 200);
        }

        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $viewerTz = config('booking.timezones.display_default', $scheduleTz);

        $year = (int) $data['year'];
        $month = (int) $data['month'];

        // [Architecture Note]: This entire manual loop and calendar logic should ideally be
        // delegated to something like `$engine->getMonthlyAvailability($year, $month, ...)`

        // تشخیص خودکار نوع تقویم:
        // - اگر سال بزرگ (>=1700) بود => میلادی
        // - اگر سال کوچک‌تر بود => جلالی (معمولاً 13xx/14xx)
        $isGregorian = $year >= 1700;

        // بازه ماه را به Carbon (میلادی) تبدیل می‌کنیم
        if ($isGregorian) {
            $start = Carbon::create($year, $month, 1, 0, 0, 0, $scheduleTz);
            if (! $start) {
                return response()->json(['data' => []], 200);
            }
            $end = $start->copy()->endOfMonth();
        } else {
            // Jalali year/month -> Gregorian start/end
            $start = $this->parseFlexibleLocalDate(sprintf('%04d-%02d-01', $year, $month), $scheduleTz);
            if (! $start) {
                return response()->json(['data' => []], 200);
            }

            // طول ماه جلالی را محاسبه می‌کنیم با تبدیل روز 1..31 تا وقتی ماه عوض شود
            $end = null;
            for ($d = 1; $d <= 31; $d++) {
                $tmp = $this->parseFlexibleLocalDate(sprintf('%04d-%02d-%02d', $year, $month, $d), $scheduleTz);
                if (! $tmp) {
                    break;
                }
                $end = $tmp;
            }
            if (! $end) {
                return response()->json(['data' => []], 200);
            }
        }

        Log::info('[Booking][OnlineCalendar] request', [
            'service_id' => $service->id,
            'provider_user_id' => $providerId,
            'year' => $year,
            'month' => $month,
            'calendar' => $isGregorian ? 'gregorian' : 'jalali',
            'range_start' => $start->toDateString(),
            'range_end' => $end->toDateString(),
        ]);

        $days = [];
        $cursor = $start->copy()->startOfDay();
        $endDay = $end->copy()->startOfDay();

        $safety = 0;

        while ($cursor->lte($endDay) && $safety < 40) {
            $dateStr = $cursor->toDateString(); // Gregorian ISO Y-m-d

            try {
                $slots = $engine->generateSlots(
                    $service->id,
                    $providerId,
                    $dateStr,
                    $dateStr,
                    viewerTimezone: $viewerTz
                );
            } catch (\Throwable $e) {
                Log::warning('[Booking][OnlineCalendar] generateSlots failed', [
                    'service_id' => $service->id,
                    'provider_user_id' => $providerId,
                    'date' => $dateStr,
                    'error' => $e->getMessage(),
                ]);
                $slots = [];
            }

            $has = ! empty($slots);

            $days[] = [
                'local_date' => $dateStr,
                'is_closed' => ! $has,
                'has_available_slots' => $has,
            ];

            $cursor->addDay();
            $safety++;
        }

        return response()->json([
            'data' => $days,
            'meta' => [
                'calendar' => $isGregorian ? 'gregorian' : 'jalali',
                'schedule_tz' => $scheduleTz,
                'viewer_tz' => $viewerTz,
            ],
        ], 200);
    }

    public function slots(Request $request, BookingService $service, BookingEngine $engine)
    {
        $settings = BookingSetting::current();
        if (! $settings->global_online_booking_enabled) {
            return response()->json(['data' => []], 200);
        }

        $data = $request->validate([
            'provider_user_id' => ['required', 'integer'],
            'date_local' => ['required', 'string'],
        ]);

        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');

        $localDate = $this->parseFlexibleLocalDate($data['date_local'], $scheduleTz);
        if (! $localDate) {
            return response()->json(['data' => []], 200);
        }

        $providerId = (int) $data['provider_user_id'];

        Log::info('[Booking][OnlineSlots] request', [
            'service_id' => $service->id,
            'provider_user_id' => $providerId,
            'date_local' => $data['date_local'],
            'date_gregorian' => $localDate->toDateString(),
        ]);

        $slots = $engine->generateSlots(
            $service->id,
            $providerId,
            $localDate->toDateString(),
            $localDate->toDateString(),
            viewerTimezone: config('booking.timezones.display_default', $scheduleTz)
        );

        return response()->json(['data' => $slots]);
    }

    public function book(Request $request, BookingService $service, AppointmentService $appointmentService, BookingEngine $engine)
    {
        $settings = BookingSetting::current();
        if (! $settings->global_online_booking_enabled) {
            return back()->withErrors(['service_id' => 'رزرو آنلاین در حال حاضر غیرفعال است.']);
        }

        $client = Auth::guard('client')->user();

        $rules = [
            'provider_user_id' => ['required', 'integer'],
            'date_local' => ['required', 'string'],
            'start_at_utc' => ['required', 'date'],
            'end_at_utc' => ['required', 'date'],
        ];

        $messages = [
            'provider_user_id.required' => 'انتخاب ارائه‌دهنده الزامی است.',
            'date_local.required' => 'انتخاب تاریخ الزامی است.',
            'start_at_utc.required' => 'انتخاب زمان الزامی است.',
            'end_at_utc.required' => 'انتخاب زمان الزامی است.',
            'full_name.required' => 'نام و نام خانوادگی الزامی است.',
            'phone.required' => 'شماره تماس الزامی است.',
            'password.required' => 'رمز عبور الزامی است.',
            'password.min' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.',
            'otp_code.required' => 'کد تایید الزامی است.',
        ];

        $loginType = null;
        if (! $client) {
            $clientMode = ClientSetting::getValue('auth.mode', 'password');
            $loginType = $request->input('login_type', $clientMode === 'both' ? ClientSetting::getValue('auth.default', 'password') : $clientMode);

            $rules['full_name'] = ['required', 'string', 'max:255'];
            $rules['phone'] = ['required', 'string', 'max:50'];

            if ($loginType === 'password') {
                $rules['password'] = ['required', 'string', 'min:6'];
            } else {
                $rules['otp_code'] = ['required', 'string'];
            }
        }

        $data = $request->validate($rules, $messages);

        // [Architecture Note]: Authentication for guest logic (Checking hash, dealing with Otp validation)
        // is better placed inside an AuthService (e.g. `$client = $authService->authenticateOrRegisterGuest(...)`).
        if (!$client) {
            $phone = $data['phone'];
            $fullName = $data['full_name'];

            if ($loginType === 'otp') {
                $code = $data['otp_code'];
                $context = 'booking_client';

                $clientRecord = Client::where('phone', $phone)->first();
                if (!$clientRecord) {
                    return back()->withErrors(['otp_code' => 'کاربری با این شماره یافت نشد. ابتدا درخواست کد دهید.'])->withInput();
                }

                $otp = SmsOtp::query()
                    ->where('phone', $phone)
                    ->where('client_id', $clientRecord->id)
                    ->where('context', $context)
                    ->where('code', $code)
                    ->latest()
                    ->first();

                if (! $otp || $otp->isExpired() || $otp->isUsed()) {
                    return back()->withErrors(['otp_code' => 'کد تایید نامعتبر است یا منقضی شده است.'])->withInput();
                }

                $otp->update(['used_at' => now()]);

                // Login the client
                Auth::guard('client')->login($clientRecord);
                $request->session()->regenerate();
                $client = Auth::guard('client')->user();
            } else {
                // Password Login / Register
                $clientRecord = Client::where('phone', $phone)->first();

                if ($clientRecord) {
                    // Login
                    if (! Hash::check($data['password'], $clientRecord->password)) {
                        return back()->withErrors(['password' => 'رمز عبور اشتباه است. اگر رمز را فراموش کرده‌اید، از ورود با پیامک استفاده کنید.'])->withInput();
                    }
                } else {
                    // Register
                    $clientRecord = Client::create([
                        'phone' => $phone,
                        'username' => $phone,
                        'full_name' => $fullName,
                        'password' => Hash::make($data['password']),
                    ]);
                }

                Auth::guard('client')->login($clientRecord);
                $request->session()->regenerate();
                $client = Auth::guard('client')->user();
            }
        }

        $providerId = (int) $data['provider_user_id'];
        if (! $engine->isOnlineBookingEnabled($service->id, $providerId)) {
            return back()->withErrors(['provider_user_id' => 'رزرو آنلاین برای این سرویس/ارائه‌دهنده فعال نیست.']);
        }

        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');

        $localDate = $this->parseFlexibleLocalDate($data['date_local'], $scheduleTz);
        if (! $localDate) {
            return back()->withErrors(['date_local' => 'تاریخ وارد شده معتبر نیست.'])->withInput();
        }

        $startUtc = Carbon::parse($data['start_at_utc'], 'UTC');
        $endUtc = Carbon::parse($data['end_at_utc'], 'UTC');

        $slots = $engine->generateSlots(
            $service->id,
            $providerId,
            $localDate->toDateString(),
            $localDate->toDateString(),
            viewerTimezone: config('booking.timezones.display_default', $scheduleTz)
        );

        $slotMatched = collect($slots)->first(function ($slot) use ($startUtc, $endUtc) {
            return ($slot['start_at_utc'] ?? null) === $startUtc->toIso8601String()
                && ($slot['end_at_utc'] ?? null) === $endUtc->toIso8601String();
        });

        if (! $slotMatched) {
            return back()
                ->withErrors(['start_at_utc' => 'اسلات انتخاب‌شده معتبر نیست یا ظرفیت ندارد.'])
                ->withInput();
        }

        // Validate Form Data
        $service->load('appointmentForm');
        $formResponse = null;
        if ($service->appointmentForm && is_array($service->appointmentForm->schema_json)) {
            $formData = $request->input('form_data', []);
            $errors = [];
            $fields = $service->appointmentForm->schema_json['fields'] ?? [];

            foreach ($fields as $field) {
                $name = $field['name'] ?? null;
                $collectFromOnline = !empty($field['collect_from_online']);

                if (!$collectFromOnline) {
                    continue;
                }

                $label = $field['label'] ?? $name;
                $required = $field['required'] ?? false;

                if ($name && $required && empty($formData[$name])) {
                    $errors["form_data.{$name}"] = "فیلد {$label} الزامی است.";
                }
            }

            if (!empty($errors)) {
                return back()->withErrors($errors)->withInput();
            }
            $formResponse = $formData;
        }

        try {
            $hold = $appointmentService->startOnlineHold(
                $service->id,
                $providerId,
                $startUtc->toIso8601String(),
                $endUtc->toIso8601String(),
                $request->session()->getId()
            );
        } catch (\RuntimeException $e) {
            $message = match ($e->getMessage()) {
                'Slot capacity is full.' => 'ظرفیت این بازه زمانی تکمیل است.',
                'Day capacity is full.' => 'ظرفیت روز تکمیل است.',
                'This day is closed.' => 'این روز بسته است.',
                'Slot is outside work windows.' => 'این بازه خارج از ساعات کاری است.',
                'Slot overlaps with break.' => 'این بازه با زمان استراحت تداخل دارد.',
                'Slot crosses day boundary.' => 'بازه انتخابی باید داخل همان روز باشد.',
                default => 'امکان رزرو در این بازه وجود ندارد.',
            };

            return back()->withErrors(['start_at_utc' => $message])->withInput();
        }

        $clientInput = ['notes' => null, 'client_id' => $client->id];

        try {
            $result = $appointmentService->confirmOnlineHold(
                $hold->id,
                $clientInput,
                appointmentFormResponse: $formResponse,
                payNow: true
            );
        } catch (\Throwable $e) {
            Log::error('[Booking][OnlineBooking] confirm failed', [
                'hold_id' => $hold->id,
                'service_id' => $service->id,
                'provider_user_id' => $providerId,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return back()->withErrors(['start_at_utc' => 'خطا در ثبت نوبت. لطفاً دوباره تلاش کنید.'])->withInput();
        }

        if (!empty($result['gateway']['payment_url'])) {
            return redirect($result['gateway']['payment_url']);
        }

        Log::info('[Booking][OnlineBooking] appointment confirmed', [
            'appointment_id' => $result['appointment']->id ?? null,
            'service_id' => $service->id,
            'provider_user_id' => $providerId,
            'client_id' => $result['appointment']->client_id ?? $client?->id,
            'start_at_utc' => $startUtc->toIso8601String(),
            'end_at_utc' => $endUtc->toIso8601String(),
        ]);

        return redirect()
            ->route('booking.public.result', $result['appointment']->id)
            ->with('success', 'نوبت شما با موفقیت ثبت شد.');
    }

    public function verifyPayment(Request $request, $gateway, BookingPayment $payment, AppointmentService $appointmentService, PaymentService $paymentService)
    {
        // کدهای پردازشی از کنترلر به سرویس منتقل شد تا کنترلر تمیز و قابل نگهداری باشد
        $result = $paymentService->verifyGatewayPayment($payment, $gateway, $request->query(), $appointmentService);

        if (!$result['valid']) {
            return redirect()->route('booking.public.index')->with('error', $result['message']);
        }

        if ($result['success']) {
            return redirect()->route('booking.public.result', $payment->appointment_id)
                ->with('success', $result['message']);
        }

        return redirect()->route('booking.public.result', $payment->appointment_id)
            ->with('error', $result['message']);
    }

    public function result(Appointment $appointment)
    {
        $appointment->load(['service', 'provider', 'payments']);
        return view('booking::web.result', compact('appointment'));
    }

    /**
     * Parses date_local that may be:
     * - Gregorian ISO: 2025-12-23
     * - Jalali: 1404-10-02 (or with / separators)
     *
     * Auto-detect based on year.
     * Returns Carbon in $tz at 00:00:00.
     */
    protected function parseFlexibleLocalDate(string $value, string $tz): ?Carbon
    {
        $value = trim($value);

        $datePieces = preg_split('/[^\d]+/', $value);
        if (count($datePieces) < 3) {
            return null;
        }

        [$y, $m, $d] = array_map('intval', array_slice($datePieces, 0, 3));

        // Basic guard
        if ($m < 1 || $m > 12 || $d < 1 || $d > 31) {
            return null;
        }

        try {
            // Gregorian
            if ($y >= 1700) {
                return Carbon::create($y, $m, $d, 0, 0, 0, $tz);
            }

            // Jalali -> Gregorian
            [$gy, $gm, $gd] = CalendarUtils::toGregorian($y, $m, $d);
            return Carbon::create($gy, $gm, $gd, 0, 0, 0, $tz);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function combineLocalDateAndTime(?Carbon $date, string $time): ?Carbon
    {
        if (! $date || empty($time)) {
            return null;
        }

        $timePieces = preg_split('/[^\d]+/', trim($time));
        if (count($timePieces) < 2) {
            return null;
        }

        $hour = min(max((int) $timePieces[0], 0), 23);
        $minute = min(max((int) $timePieces[1], 0), 59);

        return $date->copy()->setTime($hour, $minute, 0);
    }
}
