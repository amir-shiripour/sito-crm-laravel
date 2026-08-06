<?php

namespace Modules\Clients\App\Livewire\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Modules\Clients\Entities\ClientSetting;

#[Layout('layouts.user')]
class ClientDashboardSettings extends Component
{
    public string $activeTab = 'terms';

    // تنظیمات قوانین
    public bool $termsEnabled = false;
    public string $termsTitle = 'قوانین و مقررات استفاده از پرتال';
    public string $termsContent = '';
    public string $termsVersion = '1.0';
    public string $termsBtnAccept = 'قوانین را می‌پذیرم';
    public string $termsBtnLater = 'بعداً می‌خوانم';
    public bool $termsAllowLater = true;
    public bool $termsForceScroll = true;

    public function mount(): void
    {
        $this->termsEnabled     = (bool) ClientSetting::getValue('dashboard.terms.enabled', false);
        $this->termsTitle       = (string) ClientSetting::getValue('dashboard.terms.title', 'قوانین و مقررات استفاده از پرتال');
        $this->termsContent     = (string) ClientSetting::getValue('dashboard.terms.content', '');
        $this->termsVersion     = (string) ClientSetting::getValue('dashboard.terms.version', '1.0');
        $this->termsBtnAccept   = (string) ClientSetting::getValue('dashboard.terms.btn_accept', 'قوانین را می‌پذیرم');
        $this->termsBtnLater    = (string) ClientSetting::getValue('dashboard.terms.btn_later', 'بعداً می‌خوانم');
        $this->termsAllowLater  = (bool) ClientSetting::getValue('dashboard.terms.allow_later', true);
        $this->termsForceScroll = (bool) ClientSetting::getValue('dashboard.terms.force_scroll', true);
    }

    public function saveTerms(): void
    {
        $this->validate([
            'termsTitle'       => ['required', 'string', 'max:255'],
            'termsVersion'     => ['required', 'string', 'max:50'],
            'termsBtnAccept'   => ['required', 'string', 'max:100'],
            'termsBtnLater'    => ['required', 'string', 'max:100'],
        ]);

        ClientSetting::setValue('dashboard.terms.enabled', $this->termsEnabled);
        ClientSetting::setValue('dashboard.terms.title', $this->termsTitle);
        ClientSetting::setValue('dashboard.terms.content', $this->termsContent);
        ClientSetting::setValue('dashboard.terms.version', trim($this->termsVersion));
        ClientSetting::setValue('dashboard.terms.btn_accept', $this->termsBtnAccept);
        ClientSetting::setValue('dashboard.terms.btn_later', $this->termsBtnLater);
        ClientSetting::setValue('dashboard.terms.allow_later', $this->termsAllowLater);
        ClientSetting::setValue('dashboard.terms.force_scroll', $this->termsForceScroll);

        session()->flash('success', 'تنظیمات قوانین و مقررات داشبورد کلاینت با موفقیت ذخیره شد.');
    }

    public function render()
    {
        return view('clients::user.settings.dashboard');
    }
}
