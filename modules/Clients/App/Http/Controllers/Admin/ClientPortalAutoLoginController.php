<?php

namespace Modules\Clients\App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\URL;
use Modules\Clients\Entities\Client;

class ClientPortalAutoLoginController extends Controller
{
    public function __invoke(Client $client)
    {
        // لینک امضا شده با اعتبار ۵ دقیقه
        $signedUrl = URL::temporarySignedRoute(
            'clients.portal.signed-login',
            now()->addMinutes(5),
            ['client' => $client->id]
        );

        // ریدایرکت کاربر ادمین به لینک امضا شده (باز می‌شود در همان تب؛
        // چون ما در Blade target="_blank" گذاشتیم، در تب جدید باز می‌شود)
        return redirect()->away($signedUrl);
    }
}
