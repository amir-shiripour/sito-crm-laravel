<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RegistrationOtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;

class ProfileMobileOtpController extends Controller
{
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => ['required', 'string'],
        ]);

        $user = $request->user();
        $phone = RegistrationOtpService::normalizePhoneNumber($request->input('mobile'));

        if (strlen($phone) !== 11 || !str_starts_with($phone, '09')) {
            return response()->json([
                'success' => false,
                'message' => 'شماره موبایل وارد شده معتبر نیست. لطفاً یک شماره معتبر (مانند ۰۹۱۲۳۴۵۶۷۸۹) وارد کنید.',
            ], 422);
        }

        if ($phone === $user->mobile) {
            return response()->json([
                'success' => false,
                'message' => 'شماره موبایل جدید با شماره فعلی شما یکسان است.',
            ], 422);
        }

        if (User::where('mobile', $phone)->where('id', '!=', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'این شماره موبایل قبلاً برای حساب کاربری دیگری ثبت شده است.',
            ], 422);
        }

        $result = RegistrationOtpService::sendOtp($phone);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => ['required', 'string'],
            'code'   => ['required', 'string'],
        ]);

        $user = $request->user();
        $phone = RegistrationOtpService::normalizePhoneNumber($request->input('mobile'));

        if (User::where('mobile', $phone)->where('id', '!=', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'این شماره موبایل قبلاً برای حساب کاربری دیگری ثبت شده است.',
            ], 422);
        }

        $result = RegistrationOtpService::verifyOtp($phone, (string) $request->input('code'));

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        $user->forceFill(['mobile' => $phone])->save();

        if (
            Module::has('FieldService')
            && Module::isEnabled('FieldService')
            && class_exists(\Modules\FieldService\App\Models\Technician::class)
        ) {
            \Modules\FieldService\App\Models\Technician::where('user_id', $user->id)
                ->update(['mobile' => $phone]);
        }

        return response()->json([
            'success' => true,
            'phone'   => $phone,
            'message' => 'شماره موبایل جدید با موفقیت تایید و در حساب کاربری شما ثبت شد.',
        ]);
    }
}
