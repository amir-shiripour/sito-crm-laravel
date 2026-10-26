<?php

namespace Modules\FieldService\App\Livewire\Technician;

use Livewire\Component;
use Modules\FieldService\App\Models\Technician;

class DutyToggle extends Component
{
    public ?int $technicianId = null;
    public bool $isOnline = false;
    public string $status = 'active';

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
            $this->status = $tech->status instanceof \Modules\FieldService\App\Enums\TechnicianStatus
                ? $tech->status->value
                : (string) $tech->status;
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
            // Guard: Must be approved / active to toggle online
            if ($tech->status !== \Modules\FieldService\App\Enums\TechnicianStatus::ACTIVE) {
                $this->isOnline = false;
                $this->status = $tech->status instanceof \Modules\FieldService\App\Enums\TechnicianStatus
                    ? $tech->status->value
                    : (string) $tech->status;
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'text' => 'حساب شما در انتظار بررسی و تایید مدارک توسط ناظران ناوگان می‌باشد و امکان آنلاین شدن وجود ندارد.',
                ]);
                return;
            }

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
