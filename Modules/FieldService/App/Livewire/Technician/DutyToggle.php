<?php

namespace Modules\FieldService\App\Livewire\Technician;

use Livewire\Component;
use Modules\FieldService\App\Models\Technician;

class DutyToggle extends Component
{
    public ?int $technicianId = null;
    public bool $isOnline = false;

    public function mount(?int $technicianId = null)
    {
        $tech = null;
        if ($technicianId) {
            $tech = Technician::find($technicianId);
        } elseif (auth()->check()) {
            $tech = Technician::where('user_id', auth()->id())->first();
        }

        if ($tech) {
            $this->technicianId = $tech->id;
            $this->isOnline = (bool) $tech->is_online;
        }
    }

    public function toggleOnline()
    {
        if (!$this->technicianId) {
            $this->dispatch('open-technician-auth');
            return;
        }

        $tech = Technician::find($this->technicianId);
        if ($tech) {
            $tech->is_online = !$tech->is_online;
            $tech->last_active_at = now();
            $tech->save();
            $this->isOnline = $tech->is_online;

            $this->dispatch('technician-duty-changed', ['isOnline' => $this->isOnline]);

            $this->dispatch('notify', [
                'type' => $this->isOnline ? 'success' : 'info',
                'text' => $this->isOnline ? 'شما آماده دریافت ماموریت و آنلاین هستید.' : 'وضعیت شما به آفلاین تغییر یافت.',
            ]);
        }
    }

    public function render()
    {
        return view('fieldservice::livewire.technician.duty-toggle');
    }
}
