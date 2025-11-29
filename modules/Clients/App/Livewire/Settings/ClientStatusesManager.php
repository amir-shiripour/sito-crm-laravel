<?php

namespace Modules\Clients\App\Livewire\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Modules\Clients\Entities\ClientStatus;
use Modules\Clients\Entities\Client;

#[Layout('layouts.user')]
class ClientStatusesManager extends Component
{
    /** @var \Illuminate\Support\Collection */
    public $statuses;

    public ?int $editingId = null;

    public string $key = '';
    public string $label = '';
    public ?string $color = '#10b981';

    public bool $is_system = false;   // فقط جهت نمایش در UI
    public bool $is_active = true;
    public bool $show_in_quick = true;

    public int $sort_order = 0;

    /** @var array<string>  لیست کلیدهایی که از آن‌ها می‌شود به این وضعیت رسید */
    public array $allowed_from = [];

    public function mount(): void
    {
        $this->loadStatuses();
        $this->resetForm();
    }

    protected function loadStatuses(): void
    {
        $this->statuses = ClientStatus::query()
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
        $this->show_in_quick = true;
        $this->sort_order    = 0;
        $this->allowed_from  = [];
    }

    public function createNew(): void
    {
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $status = ClientStatus::findOrFail($id);

        $this->editingId     = $status->id;
        $this->key           = $status->key;
        $this->label         = $status->label;
        $this->color         = $status->color;
        $this->is_system     = (bool)$status->is_system;
        $this->is_active     = (bool)$status->is_active;
        $this->show_in_quick = (bool)$status->show_in_quick;
        $this->sort_order    = (int)($status->sort_order ?? 0);
        $this->allowed_from  = $status->allowed_from ?? [];
    }

    protected function rules(): array
    {
        return [
            'key'   => [
                'required',
                'alpha_dash',
                'max:50',
                'unique:client_statuses,key,' . ($this->editingId ?? 'NULL') . ',id',
            ],
            'label'         => ['required', 'string', 'max:100'],
            'color'         => ['nullable', 'string', 'max:20'],
            'is_active'     => ['boolean'],
            'show_in_quick' => ['boolean'],
            'sort_order'    => ['nullable', 'integer'],
            'allowed_from'  => ['array'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        // اگر داریم ویرایش می‌کنیم
        if ($this->editingId) {
            $status = ClientStatus::findOrFail($this->editingId);

            // اگر وضعیت سیستمی است، key را قفل کن
            if ($status->is_system) {
                unset($data['key']);
            }

            $status->fill($data);
            $status->save();
        } else {
            // وضعیت‌های ساخته‌شده از این صفحه، سیستمی نیستند
            $data['is_system'] = false;
            $status = ClientStatus::create($data);
            $this->editingId = $status->id;
        }

        $this->loadStatuses();
        $this->dispatch('notify', type: 'success', text: 'وضعیت با موفقیت ذخیره شد.');
    }

    public function delete(int $id): void
    {
        $status = ClientStatus::findOrFail($id);

        if ($status->is_system) {
            $this->dispatch('notify', type: 'error', text: 'امکان حذف وضعیت سیستمی وجود ندارد.');
            return;
        }

        // اگر کلاینتی این وضعیت را دارد، اجازه حذف نده
        $hasClients = Client::where('status_id', $status->id)->exists();
        if ($hasClients) {
            $this->dispatch('notify', type: 'error', text: 'این وضعیت به برخی پرونده‌ها متصل است و قابل حذف نیست.');
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
        return view('clients::user.settings.statuses');
    }
}
