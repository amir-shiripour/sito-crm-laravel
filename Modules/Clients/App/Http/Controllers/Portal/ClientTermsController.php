<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Clients\Entities\ClientSetting;
use Modules\Clients\Entities\ClientTermsAcceptance;

class ClientTermsController extends Controller
{
    public function accept(Request $request)
    {
        $client = auth('client')->user();

        if (!$client) {
            return response()->json(['success' => false, 'message' => 'کاربر یافت نشد.'], 401);
        }

        $version = (string) ClientSetting::getValue('dashboard.terms.version', '1.0');

        ClientTermsAcceptance::updateOrCreate(
            [
                'client_id' => $client->id,
                'version'   => $version,
            ],
            [
                'accepted_at' => now(),
                'ip_address'  => $request->ip(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'قوانین و مقررات با موفقیت تایید شد.'
        ]);
    }
}
