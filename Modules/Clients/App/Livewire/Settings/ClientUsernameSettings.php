<?php

namespace Modules\Clients\App\Livewire\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Modules\Clients\Entities\ClientSetting;

#[Layout('layouts.user')]
class ClientUsernameSettings extends Component
{
    /**
     * مقدار مورد استفاده در UI:
     * email_local | mobile | national_code | name_rand | prefix_incremental
     */
    public $strategy = 'email_local';

    /**
     * پیشوند در حالت prefix_incremental
     */
    public $prefix   = 'clt';

    public function mount()
    {
        // مقدار ذخیره‌شده‌ی canonical در تنظیمات
        // اول از کلیدهای جدید می‌خوانیم، اگر نبود از کلیدهای قدیمی نقطه‌دار
        $canonical = ClientSetting::getValue('username_strategy')
            ?? ClientSetting::getValue('username.strategy', 'email_local');

        // مپ canonical → UI
        $this->strategy = match ($canonical) {
            'mobile'          => 'mobile',
            'national_code'   => 'national_code',
            'name_increment'  => 'name_rand',
            'prefix_increment'=> 'prefix_incremental',
            'email_local'     => 'email_local',
            'email'           => 'email_local', // اگر قبلاً به این شکل ذخیره شده باشد
            default           => 'email_local',
        };

        // prefix فعلی (با fallback به کلید قدیمی)
        $this->prefix = ClientSetting::getValue('username_prefix')
            ?? ClientSetting::getValue('username.prefix', 'clt');
    }

    public function save()
    {
        // map UI -> canonical
        $map = [
            'email_local'        => 'email_local',
            'mobile'             => 'mobile',
            'national_code'      => 'national_code',
            'name_rand'          => 'name_increment',
            'prefix_incremental' => 'prefix_increment',
        ];

        $canonical = $map[$this->strategy] ?? 'email_local';

        // ذخیره در کلیدهای جدید (و برای سازگاری، در کلیدهای قدیمی نقطه‌دار)
        ClientSetting::setValue('username_strategy', $canonical);
        ClientSetting::setValue('username.strategy', $canonical);

        if ($canonical === 'prefix_increment') {
            $prefix = $this->prefix ?: 'clt';

            ClientSetting::setValue('username_prefix', $prefix);
            ClientSetting::setValue('username.prefix', $prefix);
        }

        session()->flash('success', 'ذخیره شد.');
    }

    public function render()
    {
        return view('clients::user.settings.username');
    }
}
