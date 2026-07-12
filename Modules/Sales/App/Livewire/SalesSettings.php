<?php

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Modules\Sales\App\Models\SalesSetting;

class SalesSettings extends Component
{
    public bool $autoCreateDeal = false;

    public function mount()
    {
        $this->autoCreateDeal = (bool) SalesSetting::getValue('auto_create_deal', false);
    }

    public function saveSettings()
    {
        SalesSetting::setValue('auto_create_deal', $this->autoCreateDeal);
        $this->dispatch('notify', message: 'تنظیمات فروش با موفقیت ذخیره شد.', type: 'success');
    }

    public function render()
    {
        return view('sales::livewire.sales-settings');
    }
}
