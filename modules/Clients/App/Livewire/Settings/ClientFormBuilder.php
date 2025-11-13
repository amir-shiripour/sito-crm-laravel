<?php

namespace Modules\Clients\App\Livewire\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Modules\Clients\Entities\ClientForm as ClientFormSchema;

#[Layout('layouts.user')]
class ClientFormBuilder extends Component
{
    public $forms;
    public $activeFormId;
    public $name = '';
    public $key  = '';
    public $is_default = false;
    public $schema = ['fields' => []];

    public function mount()
    {
        $this->forms = ClientFormSchema::all();

        if ($def = ClientFormSchema::default()) {
            $this->loadForm($def->id);
        }
    }

    public function loadForm($id)
    {
        $f = ClientFormSchema::findOrFail($id);
        $this->activeFormId = $id;
        $this->name = $f->name;
        $this->key  = $f->key;
        $this->is_default = (bool) $f->is_default;
        $this->schema = $f->schema ?? ['fields' => []];
    }

    public function addField($type)
    {
        $id = $type.'_'.substr((string) \Illuminate\Support\Str::uuid(), 0, 8);
        $this->schema['fields'][] = [
            'type' => $type,
            'id'   => $id,
            'label'=> 'بی‌نام',
            'quick_create' => false,
        ];
    }

    public function saveForm()
    {
        $this->validate([
            'name'   => 'required|string|max:100',
            'key'    => 'required|alpha_dash|max:100|unique:client_forms,key,'.($this->activeFormId ?: 'NULL').',id',
            'schema' => 'required|array',
        ]);

        $form = ClientFormSchema::updateOrCreate(
            ['id' => $this->activeFormId],
            ['name'=>$this->name, 'key'=>$this->key, 'is_default'=>$this->is_default, 'schema'=>$this->schema]
        );

        if ($this->is_default) {
            ClientFormSchema::where('id','!=',$form->id)->update(['is_default'=>false]);
            \Modules\Clients\Entities\ClientSetting::setValue('default_form_key', $form->key);
        }

        $this->forms = ClientFormSchema::all();
        $this->dispatch('saved');
    }

    public function render()
    {
        return view('clients::user.settings.forms-builder');
    }
}
