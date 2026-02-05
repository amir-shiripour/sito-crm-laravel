<?php

namespace Modules\Booking\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Booking\Entities\BookingSetting;

class BookingSettingsController extends Controller
{
    public function editLabel()
    {
        $labelProvider = BookingSetting::getValue('label_provider', config('booking.labels.provider', 'ارائه‌دهنده'));
        $labelProviders = BookingSetting::getValue('label_providers', config('booking.labels.providers', 'ارائه‌دهندگان'));

        return view('booking::admin.settings.label', compact('labelProvider', 'labelProviders'));
    }

    public function updateLabel(Request $request)
    {
        $data = $request->validate([
            'label_provider' => 'required|string|max:50',
            'label_providers' => 'required|string|max:50',
        ]);

        BookingSetting::setValue('label_provider', $data['label_provider']);
        BookingSetting::setValue('label_providers', $data['label_providers']);

        // اعمال فوری روی کانفیگ ران‌تایم (همین ریکوئست)
        config([
            'booking.labels.provider' => $data['label_provider'],
            'booking.labels.providers' => $data['label_providers'],
        ]);

        return redirect()->route('admin.booking.settings.label.edit')
            ->with('success', 'برچسب‌ها با موفقیت ذخیره شد.');
    }
}
