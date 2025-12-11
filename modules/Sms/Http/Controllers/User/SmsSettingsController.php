<?php

namespace Modules\Sms\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sms\Entities\SmsGatewaySetting;
use Modules\Sms\Services\SmsManager;

class SmsSettingsController extends Controller
{
    public function index(Request $request, SmsManager $sms)
    {
        $user = $request->user();

        $setting = SmsGatewaySetting::query()
            ->where('user_id', $user->id)
            ->first();

        $balance = null;

        if ($setting) {
            try {
                $balance = $sms->driver($setting->driver)->fetchBalance();
            } catch (\Throwable $e) {
                $balance = null;
            }
        }

        return view('sms::user.settings.index', [
            'setting' => $setting,
            'balance' => $balance,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'driver'        => ['required', 'string', 'max:100'],
            'sender'        => ['nullable', 'string', 'max:191'],
            'api_key'       => ['nullable', 'string', 'max:191'],
            'base_url'      => ['nullable', 'string', 'max:191'],
            'config'        => ['array'],
        ]);

        $config = [
            'api_key'  => $data['api_key'] ?? null,
            'base_url' => $data['base_url'] ?? null,
        ] + ($data['config'] ?? []);

        $setting = SmsGatewaySetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'driver' => $data['driver'],
                'sender' => $data['sender'] ?? null,
                'config' => $config,
            ]
        );

        return redirect()
            ->route('user.sms.settings.index')
            ->with('status', 'تنظیمات پیامک با موفقیت ذخیره شد.');
    }
}
