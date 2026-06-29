<?php

namespace Modules\ContractForge\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ContractForge\App\Models\ContractSetting;

class ContractSettingController extends Controller
{
    public function edit()
    {
        $settings = [
            'number_format' => ContractSetting::get('number_format', 'CON-{YEAR}{MONTH}{DAY}-{COUNTER}'),
            'number_counter' => ContractSetting::get('number_counter', 1),
            'number_counter_length' => ContractSetting::get('number_counter_length', 5),
        ];

        return view('contractforge::user.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'number_format' => 'required|string|max:255',
            'number_counter' => 'required|integer|min:1',
            'number_counter_length' => 'required|integer|min:1|max:10',
        ]);

        ContractSetting::set('number_format', $request->number_format);
        ContractSetting::set('number_counter', (int) $request->number_counter);
        ContractSetting::set('number_counter_length', (int) $request->number_counter_length);

        return back()->with('success', 'تنظیمات قراردادها با موفقیت به‌روزرسانی شد.');
    }
}
