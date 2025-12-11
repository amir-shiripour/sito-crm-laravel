<?php

namespace Modules\Sms\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sms\Entities\SmsOtp;
use Modules\Sms\Services\SmsManager;

class OtpController extends Controller
{
    public function send(Request $request, SmsManager $sms)
    {
        $data = $request->validate([
            'phone'   => ['required', 'string', 'max:50'],
            'context' => ['nullable', 'string', 'max:100'],
        ]);

        $ttl = config('sms.otp.ttl', 5);

        $smsMessage = $sms->sendOtp($data['phone'], $data['context'] ?? 'login');

        $otp = SmsOtp::create([
            'phone'      => $data['phone'],
            'code'       => $smsMessage->message,
            'context'    => $data['context'] ?? 'login',
            'expires_at' => now()->addMinutes($ttl),
            'meta'       => [
                'sms_message_id' => $smsMessage->id,
            ],
        ]);

        return response()->json([
            'success' => true,
            'expires_in' => $ttl * 60,
        ]);
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'phone'   => ['required', 'string', 'max:50'],
            'code'    => ['required', 'string', 'max:20'],
            'context' => ['nullable', 'string', 'max:100'],
        ]);

        $otp = SmsOtp::query()
            ->where('phone', $data['phone'])
            ->where('code', $data['code'])
            ->when(! empty($data['context']), function ($q) use ($data) {
                $q->where('context', $data['context']);
            })
            ->latest()
            ->first();

        if (! $otp || $otp->isExpired() || $otp->isUsed()) {
            return response()->json([
                'success' => false,
                'message' => 'کد نامعتبر است یا منقضی شده است.',
            ], 422);
        }

        $otp->update([
            'used_at' => now(),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
