<?php

namespace Modules\Sms\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sms\Entities\SmsMessage;

class SmsWebhookController extends Controller
{
    /**
     * این متد می‌تواند برای دریافت وبهوک گزارش تحویل (delivery report) از سرویس‌دهنده استفاده شود.
     * ساختار پارامترها را می‌توانید مطابق با مستندات سرویس خود تنظیم کنید.
     */
    public function deliveryReport(Request $request)
    {
        $providerMessageId = $request->get('message_id');
        $status            = $request->get('status');

        if ($providerMessageId) {
            SmsMessage::query()
                ->where('meta->provider_message_id', $providerMessageId)
                ->update(['status' => $status]);
        }

        return response()->json(['ok' => true]);
    }
}
