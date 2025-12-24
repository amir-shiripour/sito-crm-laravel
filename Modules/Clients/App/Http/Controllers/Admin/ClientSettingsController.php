<?php

namespace Modules\Clients\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Clients\Entities\Client;
use Illuminate\Support\Facades\Storage;
use Modules\Clients\Entities\ClientSetting;
class ClientSettingsController extends Controller
{
    public function editLabel()
    {
        $labelSingular = ClientSetting::getValue('label_singular', config('clients.labels.singular'));
        $labelPlural   = ClientSetting::getValue('label_plural',   config('clients.labels.plural'));

        return view('clients::admin.settings.label', compact('labelSingular', 'labelPlural'));
    }

    public function updateLabel(Request $request)
    {
        $data = $request->validate([
            'label_singular' => 'required|string|max:50',
            'label_plural'   => 'required|string|max:50',
        ]);

        ClientSetting::setValue('label_singular', $data['label_singular']);
        ClientSetting::setValue('label_plural',   $data['label_plural']);

        // اعمال فوری روی کانفیگ ران‌تایم (همین ریکوئست)
        config([
            'clients.labels.singular' => $data['label_singular'],
            'clients.labels.plural'   => $data['label_plural'],
        ]);

        return redirect()->route('admin.clients.settings.label.edit')
            ->with('success', 'برچسب‌ها با موفقیت ذخیره شد.');
    }

}
