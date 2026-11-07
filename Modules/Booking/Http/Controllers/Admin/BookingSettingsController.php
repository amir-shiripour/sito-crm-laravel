<?php

namespace Modules\Booking\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Booking\Entities\BookingSetting;

class BookingSettingsController extends Controller
{
    public function editLabel()
    {
        $labelProvider  = BookingSetting::getValue('label_provider', config('booking.labels.provider', 'ارائه‌دهنده'));
        $labelProviders = BookingSetting::getValue('label_providers', config('booking.labels.providers', 'ارائه‌دهندگان'));
        $labelService   = BookingSetting::getValue('label_service', config('booking.labels.service', 'سرویس'));
        $labelServices  = BookingSetting::getValue('label_services', config('booking.labels.services', 'سرویس‌ها'));
        $labelClient    = BookingSetting::getValue('label_client', config('booking.labels.client', 'مراجع'));
        $labelClients   = BookingSetting::getValue('label_clients', config('booking.labels.clients', 'مراجعین'));

        return view('booking::admin.settings.label', compact(
            'labelProvider',
            'labelProviders',
            'labelService',
            'labelServices',
            'labelClient',
            'labelClients'
        ));
    }

    public function updateLabel(Request $request)
    {
        $data = $request->validate([
            'label_provider'  => 'required|string|max:50',
            'label_providers' => 'required|string|max:50',
            'label_service'   => 'required|string|max:50',
            'label_services'  => 'required|string|max:50',
            'label_client'    => 'required|string|max:50',
            'label_clients'   => 'required|string|max:50',
        ]);

        BookingSetting::setValue('label_provider', $data['label_provider']);
        BookingSetting::setValue('label_providers', $data['label_providers']);
        BookingSetting::setValue('label_service', $data['label_service']);
        BookingSetting::setValue('label_services', $data['label_services']);
        BookingSetting::setValue('label_client', $data['label_client']);
        BookingSetting::setValue('label_clients', $data['label_clients']);

        // اعمال فوری روی کانفیگ ران‌تایم (همین ریکوئست)
        config([
            'booking.labels.provider'  => $data['label_provider'],
            'booking.labels.providers' => $data['label_providers'],
            'booking.labels.service'   => $data['label_service'],
            'booking.labels.services'  => $data['label_services'],
            'booking.labels.client'    => $data['label_client'],
            'booking.labels.clients'   => $data['label_clients'],
        ]);

        return redirect()->route('admin.booking.settings.label.edit')
            ->with('success', 'برچسب‌ها با موفقیت ذخیره شد.');
    }
}
