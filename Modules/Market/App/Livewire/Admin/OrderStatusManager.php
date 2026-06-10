<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Modules\Market\App\Models\MarketOrderStatus;

class OrderStatusManager extends Component
{
    public $statuses;
    public $isEditing = false;
    public $editingId = null;

    // Form Fields
    public $admin_label = '';
    public $client_label = '';
    public $color_class = 'bg-gray-50 text-gray-700 border-gray-200';
    public $system_type = 'processing';
    public $show_to_client = true;
    public $show_in_client_stepper = true;
    public $show_in_admin_stepper = true;
    public $sort_order = 0;
    public $is_active = true;

    public $colorPresets = [
        'gray'    => 'bg-gray-50 text-gray-700 border-gray-200',
        'amber'   => 'bg-amber-50 text-amber-700 border-amber-200',
        'blue'    => 'bg-blue-50 text-blue-700 border-blue-200',
        'indigo'  => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'violet'  => 'bg-violet-50 text-violet-700 border-violet-200',
        'cyan'    => 'bg-cyan-50 text-cyan-700 border-cyan-200',
        'sky'     => 'bg-sky-50 text-sky-700 border-sky-200',
        'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'rose'    => 'bg-rose-50 text-rose-700 border-rose-200',
        'purple'  => 'bg-purple-50 text-purple-700 border-purple-200',
        'orange'  => 'bg-orange-50 text-orange-700 border-orange-200',
    ];

    protected $rules = [
        'admin_label' => 'required|string|max:255',
        'client_label' => 'required|string|max:255',
        'color_class' => 'required|string|max:255',
        'system_type' => 'required|string|in:pending,processing,shipped,delivered,canceled,returned',
        'show_to_client' => 'boolean',
        'show_in_client_stepper' => 'boolean',
        'show_in_admin_stepper' => 'boolean',
        'sort_order' => 'required|integer',
        'is_active' => 'boolean',
    ];

    public function mount()
    {
        $this->loadStatuses();
    }

    public function loadStatuses()
    {
        $this->statuses = MarketOrderStatus::orderBy('sort_order', 'asc')->get();
    }

    public function create()
    {
        $this->resetFields();
        $this->sort_order = ($this->statuses->max('sort_order') ?? 0) + 10;
        $this->isEditing = true;
    }

    public function edit($id)
    {
        $status = MarketOrderStatus::findOrFail($id);
        $this->editingId = $status->id;
        $this->admin_label = $status->admin_label;
        $this->client_label = $status->client_label;
        $this->color_class = $status->color_class;
        $this->system_type = $status->system_type;
        $this->show_to_client = (bool) $status->show_to_client;
        $this->show_in_client_stepper = (bool) $status->show_in_client_stepper;
        $this->show_in_admin_stepper = (bool) $status->show_in_admin_stepper;
        $this->sort_order = $status->sort_order;
        $this->is_active = $status->is_active;
        $this->isEditing = true;
    }

    public function selectColor($color)
    {
        if (isset($this->colorPresets[$color])) {
            $this->color_class = $this->colorPresets[$color];
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $status = MarketOrderStatus::findOrFail($this->editingId);
            $status->update([
                'admin_label' => $this->admin_label,
                'client_label' => $this->client_label,
                'color_class' => $this->color_class,
                'system_type' => $this->system_type,
                'show_to_client' => $this->show_to_client,
                'show_in_client_stepper' => $this->show_in_client_stepper,
                'show_in_admin_stepper' => $this->show_in_admin_stepper,
                'sort_order' => $this->sort_order,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', 'وضعیت با موفقیت بروزرسانی شد.');
        } else {
            MarketOrderStatus::create([
                'admin_label' => $this->admin_label,
                'client_label' => $this->client_label,
                'color_class' => $this->color_class,
                'system_type' => $this->system_type,
                'show_to_client' => $this->show_to_client,
                'show_in_client_stepper' => $this->show_in_client_stepper,
                'show_in_admin_stepper' => $this->show_in_admin_stepper,
                'sort_order' => $this->sort_order,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', 'وضعیت جدید با موفقیت اضافه شد.');
        }

        $this->resetFields();
        $this->loadStatuses();
    }

    public function delete($id)
    {
        $status = MarketOrderStatus::findOrFail($id);
        
        if ($status->orders()->exists()) {
            session()->flash('error', 'این وضعیت به سفارشاتی متصل است و قابل حذف نیست.');
            return;
        }

        $status->delete();
        $this->loadStatuses();
        session()->flash('success', 'وضعیت با موفقیت حذف شد.');
    }

    public function cancel()
    {
        $this->resetFields();
    }

    private function resetFields()
    {
        $this->isEditing = false;
        $this->editingId = null;
        $this->admin_label = '';
        $this->client_label = '';
        $this->color_class = 'bg-gray-50 text-gray-700 border-gray-200';
        $this->system_type = 'processing';
        $this->show_to_client = true;
        $this->show_in_client_stepper = true;
        $this->show_in_admin_stepper = true;
        $this->sort_order = 0;
        $this->is_active = true;
    }

    public function updateOrder($items)
    {
        foreach ($items as $item) {
            MarketOrderStatus::where('id', $item['value'])->update(['sort_order' => $item['order']]);
        }
        $this->loadStatuses();
    }

    public function render()
    {
        return view('market::livewire.admin.order-status-manager')->layout('layouts.user');
    }
}
