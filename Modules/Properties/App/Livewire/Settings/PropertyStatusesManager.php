<?php

namespace Modules\Properties\App\Livewire\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Modules\Properties\Entities\PropertyStatus;
use Modules\Properties\Entities\Property;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.user')]
class PropertyStatusesManager extends Component
{
    use AuthorizesRequests;

    /** @var \Illuminate\Support\Collection */
    public $statuses;

    public ?int $editingId = null;

    public string $key = '';
    public string $label = '';
    public ?string $color = '#10b981';

    public bool $is_system = false;
    public bool $is_active = true;
    public bool $is_default = false;

    public int $sort_order = 0;

    public function mount(): void
    {
        $this->authorize('properties.settings.manage');
        $this->loadStatuses();
        $this->resetForm();
    }

    protected function loadStatuses(): void
    {
        $this->statuses = PropertyStatus::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    protected function resetForm(): void
    {
        $this->editingId     = null;
        $this->key           = '';
        $this->label         = '';
        $this->color         = '#10b981';
        $this->is_system     = false;
        $this->is_active     = true;
        $this->is_default    = false;
        $this->sort_order    = 0;
    }

    public function createNew(): void
    {
        $this->authorize('properties.settings.manage');
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $this->authorize('properties.settings.manage');
        $status = PropertyStatus::findOrFail($id);

        $this->editingId     = $status->id;
        $this->key           = $status->key;
        $this->label         = $status->label;
        $this->color         = $status->color;
        $this->is_system     = (bool)$status->is_system;
        $this->is_active     = (bool)$status->is_active;
        $this->is_default    = (bool)$status->is_default;
        $this->sort_order    = (int)($status->sort_order ?? 0);
    }

    protected function rules(): array
    {
        return [
            'key'   => [
                'required',
                'alpha_dash',
                'max:50',
                'unique:property_statuses,key,' . ($this->editingId ?? 'NULL') . ',id',
            ],
            'label'         => ['required', 'string', 'max:100'],
            'color'         => ['nullable', 'string', 'max:20'],
            'is_active'     => ['boolean'],
            'is_default'    => ['boolean'],
            'sort_order'    => ['nullable', 'integer'],
        ];
    }

    public function save(): void
    {
        $this->authorize('properties.settings.manage');
        $data = $this->validate();

        // اگر این وضعیت به عنوان پیش‌فرض انتخاب شده، بقیه را از حالت پیش‌فرض خارج کن
        if ($data['is_default']) {
            PropertyStatus::where('is_default', true)->update(['is_default' => false]);
        }

        if ($this->editingId) {
            $status = PropertyStatus::findOrFail($this->editingId);

            if ($status->is_system) {
                unset($data['key']);
            }

            $status->fill($data);
            $status->save();
        } else {
            $data['is_system'] = false;
            $status = PropertyStatus::create($data);
            $this->editingId = $status->id;
        }

        $this->loadStatuses();
        $this->dispatch('notify', type: 'success', text: 'وضعیت با موفقیت ذخیره شد.');
    }

    public function delete(int $id): void
    {
        $this->authorize('properties.settings.manage');
        $status = PropertyStatus::findOrFail($id);

        if ($status->is_system) {
            $this->dispatch('notify', type: 'error', text: 'امکان حذف وضعیت سیستمی وجود ندارد.');
            return;
        }

        if ($status->is_default) {
            $this->dispatch('notify', type: 'error', text: 'امکان حذف وضعیت پیش‌فرض وجود ندارد. ابتدا وضعیت پیش‌فرض دیگری انتخاب کنید.');
            return;
        }

        $hasProperties = Property::where('status_id', $status->id)->exists();
        if ($hasProperties) {
            $this->dispatch('notify', type: 'error', text: 'این وضعیت به برخی املاک متصل است و قابل حذف نیست.');
            return;
        }

        $status->delete();

        if ($this->editingId === $id) {
            $this->resetForm();
        }

        $this->loadStatuses();
        $this->dispatch('notify', type: 'success', text: 'وضعیت حذف شد.');
    }

    public function render()
    {
        return view('properties::user.settings.statuses');
    }
}
