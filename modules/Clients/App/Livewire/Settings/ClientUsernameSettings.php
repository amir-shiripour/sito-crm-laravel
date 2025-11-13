<?php

namespace Modules\Clients\App\Livewire\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Modules\Clients\Entities\ClientSetting;

#[Layout('layouts.user')]
class ClientUsernameSettings extends Component
{
    public $strategy = 'email_local';
    public $prefix   = 'user';

    public function mount()
    {
        $this->strategy = ClientSetting::getValue('username.strategy', 'email_local');
        $this->prefix   = ClientSetting::getValue('username.prefix',   'user');
    }

    public function save()
    {
        // map UI -> canonical
        $map = [
            'email_local'        => 'email_local',
            'mobile'             => 'mobile',
            'name_rand'          => 'name_increment',     // نام قدیمی شما
            'prefix_incremental' => 'prefix_increment',   // نام قدیمی شما
            // اگر گزینه "email" تمام ایمیل هم خواستی، به UI اضافه‌اش کن و map کن به 'email'
        ];

        $canonical = $map[$this->strategy] ?? 'email_local';

        ClientSetting::setValue('username_strategy', $canonical);
        if ($canonical === 'prefix_increment') {
            ClientSetting::setValue('username_prefix', $this->prefix ?: 'clt');
        }

        session()->flash('success', 'ذخیره شد.');
    }
    public function render()
    {
        return view('clients::user.settings.username');
    }
}
