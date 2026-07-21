<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Modules\Market\App\Models\CheckoutForm;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\Category;
use Modules\Clients\Entities\ClientForm;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CheckoutFormManager extends Component
{
    public $forms;
    public $activeFormId = null;

    // Form properties
    public string $name = '';
    public string $key = '';
    public bool $is_active = true;
    public $product_id = null;
    public $category_id = null;
    public array $schema = ['fields' => [], 'groups' => []];
    public array $activePaymentMethods = [];

    // Data for select boxes
    public $products = [];
    public $categories = [];
    public $clientFormFields = [];

    // UI state
    public bool $reorderMode = false;
    public ?array $schemaBackup = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'key' => [
                'required',
                'string',
                'max:255',
                Rule::unique('checkout_forms', 'key')->ignore($this->activeFormId),
            ],
            'is_active' => 'boolean',
            'product_id' => 'nullable|exists:market_master_products,id',
            'category_id' => 'nullable|exists:market_categories,id',
            'schema.groups.*.name' => 'required|string',
        ];
    }

    public function mount()
    {
        $this->forms = CheckoutForm::latest()->get();
        $this->products = MasterProduct::where('status', 'active')->pluck('title', 'id')->toArray();
        $this->categories = Category::pluck('name', 'id')->toArray();

        // Load active payment methods from general system settings dynamically
        $genSettings = \Modules\Settings\Entities\Setting::all()->pluck('value', 'key')->toArray();
        $activeSystemMethods = json_decode($genSettings['active_payment_methods'] ?? '[]', true);
        if (!is_array($activeSystemMethods)) $activeSystemMethods = [];

        $this->activePaymentMethods = [];
        if (in_array('online', $activeSystemMethods)) {
            if (($genSettings['zarinpal_status'] ?? 'inactive') === 'active') {
                $this->activePaymentMethods['zarinpal'] = 'زرین‌پال';
            }
            if (($genSettings['zibal_status'] ?? 'inactive') === 'active') {
                $this->activePaymentMethods['zibal'] = 'زیبال';
            }
            if (($genSettings['behpardakht_status'] ?? 'inactive') === 'active') {
                $this->activePaymentMethods['behpardakht'] = 'به پرداخت ملت';
            }
        }
        if (in_array('pos', $activeSystemMethods) && ($genSettings['pos_status'] ?? 'inactive') === 'active') {
            $this->activePaymentMethods['pos'] = 'پرداخت در محل (کارتخوان)';
        }
        if (in_array('transfer', $activeSystemMethods) && ($genSettings['bank_transfer_status'] ?? 'inactive') === 'active') {
            $this->activePaymentMethods['transfer'] = 'کارت به کارت / فیش بانکی';
        }
        if (in_array('cod', $activeSystemMethods) && ($genSettings['cod_status'] ?? 'inactive') === 'active') {
            $this->activePaymentMethods['cod'] = 'پرداخت در محل (نقدی)';
        }

        $clientForm = ClientForm::active() ?? ClientForm::first();
        if ($clientForm) {
            $this->clientFormFields = collect($clientForm->schema['fields'] ?? [])
                ->map(fn($field) => ['id' => $field['id'], 'label' => $field['label']])
                ->all();
        }

        if ($this->forms->isNotEmpty()) {
            $this->selectForm($this->forms->first()->id);
        }
    }

    public function selectForm($formId)
    {
        if ($formId) {
            $form = CheckoutForm::find($formId);
            if ($form) {
                $this->activeFormId = $formId;
                $this->name = $form->name;
                $this->key = $form->key;
                $this->is_active = $form->is_active;
                $this->product_id = $form->product_id;
                $this->category_id = $form->category_id;
                $this->schema = $form->schema ?? ['fields' => [], 'groups' => []];

                if (empty($this->schema['groups']) && !empty($this->schema['fields'])) {
                    $this->regenerateGroupsFromFields();
                }
            }
        } else {
            $this->resetForm();
        }
        $this->reorderMode = false;
    }

    public function createNewForm()
    {
        $this->resetForm();
        $this->activeFormId = 'new';
    }

    public function resetForm()
    {
        $this->activeFormId = null;
        $this->name = '';
        $this->key = '';
        $this->is_active = true;
        $this->product_id = null;
        $this->category_id = null;
        $this->schema = ['fields' => [], 'groups' => [['id' => 'group_1', 'name' => 'اطلاعات تکمیلی']]];
    }

    public function addField($type)
    {
        $defaultGroup = $this->schema['groups'][0]['id'] ?? 'default';
        if ($defaultGroup === 'default' && !empty($this->schema['groups'])) {
            $defaultGroup = $this->schema['groups'][0]['id'];
        } elseif (empty($this->schema['groups'])) {
            $newGroupId = 'group_' . Str::random(4);
            $this->schema['groups'][] = ['id' => $newGroupId, 'name' => 'اطلاعات تکمیلی'];
            $defaultGroup = $newGroupId;
        }

        $newField = [
            'id' => 'field_' . Str::random(6),
            'type' => $type,
            'label' => 'فیلد جدید',
            'placeholder' => '',
            'group' => $defaultGroup,
            'width' => 'full',
            'required' => false,
            'required_payment_methods' => [],
            'source' => 'meta',
            'sync' => null,
            'validation' => [],
        ];

        if ($type === 'select') {
            $newField['options'] = [['value' => 'option1', 'label' => 'گزینه ۱']];
        }
        if ($type === 'postal-code') {
            $newField['label'] = 'کد پستی';
            $newField['validation'] = ['regex:/^([0-9]{10})$/'];
        }

        $this->schema['fields'][] = $newField;
    }

    public function removeField($index)
    {
        if (isset($this->schema['fields'][$index])) {
            unset($this->schema['fields'][$index]);
            $this->schema['fields'] = array_values($this->schema['fields']);
        }
    }

    public function addSystemField($id)
    {
        if (collect($this->schema['fields'])->contains('id', $id)) return;

        $systemDefaults = CheckoutForm::systemFieldDefaults();
        if (!isset($systemDefaults[$id])) return;

        $field = $systemDefaults[$id];
        $groupName = $field['group'] ?? 'اطلاعات سیستمی';

        $groups = collect($this->schema['groups'] ?? []);
        $existingGroup = $groups->firstWhere('name', $groupName);

        if ($existingGroup) {
            $field['group'] = $existingGroup['id'];
        } else {
            $newGroupId = 'group_' . Str::random(4);
            $this->schema['groups'][] = ['id' => $newGroupId, 'name' => $groupName];
            $field['group'] = $newGroupId;
        }

        $this->schema['fields'][] = $field;
    }

    public function addGroup()
    {
        $this->schema['groups'][] = [
            'id' => 'group_' . Str::random(4),
            'name' => 'گروه جدید'
        ];
    }

    public function removeGroup($groupId)
    {
        // Remove the group from the groups array
        $this->schema['groups'] = collect($this->schema['groups'])
            ->reject(fn($g) => $g['id'] === $groupId)
            ->values()
            ->all();

        // If there are still groups left, move fields in the deleted group to the first group.
        $targetGroupId = $this->schema['groups'][0]['id'] ?? 'default';
        if ($targetGroupId === 'default' && empty($this->schema['groups'])) {
            $targetGroupId = 'group_' . Str::random(4);
            $this->schema['groups'][] = ['id' => $targetGroupId, 'name' => 'اطلاعات تکمیلی'];
        }

        $this->schema['fields'] = collect($this->schema['fields'])
            ->map(function($field) use ($groupId, $targetGroupId) {
                if (($field['group'] ?? '') === $groupId) {
                    $field['group'] = $targetGroupId;
                }
                return $field;
            })
            ->all();

        $this->dispatch('notify', type: 'success', text: 'گروه با موفقیت حذف شد و فیلدهای آن منتقل شدند.');
    }

    public function changeFieldGroup($fieldId, $newGroupId, $newIndex)
    {
        $fieldIndex = collect($this->schema['fields'])->search(fn($f) => $f['id'] === $fieldId);

        if ($fieldIndex === false) return;

        // Update group ID
        $this->schema['fields'][$fieldIndex]['group'] = $newGroupId;

        // Reorder within the new group
        $this->reorderFields($newGroupId, array_merge(
            collect($this->schema['fields'])->where('group', $newGroupId)->where('id', '!=', $fieldId)->pluck('id')->toArray(),
            [$fieldId]
        ));
    }

    public function toggleReorderMode()
    {
        $this->reorderMode = !$this->reorderMode;
        if ($this->reorderMode) {
            $this->schemaBackup = $this->schema;
        } else {
            // When exiting reorder mode via "Save Order", save the form.
            $this->saveForm();
            $this->schemaBackup = null;
        }
    }

    public function cancelReorder()
    {
        if ($this->schemaBackup !== null) {
            $this->schema = $this->schemaBackup;
        }
        $this->reorderMode = false;
        $this->schemaBackup = null;
    }

    public function reorderGroups($newOrder)
    {
        $currentGroups = collect($this->schema['groups'] ?? []);
        $reorderedGroups = collect($newOrder)->map(function($groupId) use ($currentGroups) {
            return $currentGroups->firstWhere('id', $groupId);
        })->filter()->values()->all();
        $this->schema['groups'] = $reorderedGroups;
    }

    public function reorderFields($groupId, $newOrder)
    {
        $fields = collect($this->schema['fields']);
        $fieldsInGroup = $fields->where('group', $groupId);
        $otherFields = $fields->where('group', '!=', $groupId);

        $reorderedInGroup = collect($newOrder)->map(function($fieldId) use ($fieldsInGroup) {
            return $fieldsInGroup->firstWhere('id', $fieldId);
        })->filter();

        $this->schema['fields'] = $otherFields->merge($reorderedInGroup)->values()->all();
    }

    public function saveForm()
    {
        $this->validate();
        $this->schema = CheckoutForm::normalizeSchema($this->schema);
        $formId = ($this->activeFormId === 'new') ? null : $this->activeFormId;

        $form = CheckoutForm::updateOrCreate(
            ['id' => $formId],
            [
                'name' => $this->name,
                'key' => $this->key,
                'is_active' => $this->is_active,
                'product_id' => $this->product_id,
                'category_id' => $this->category_id,
                'schema' => $this->schema,
            ]
        );

        $this->forms = CheckoutForm::latest()->get();
        if ($this->activeFormId === 'new') {
            $this->selectForm($form->id);
        }

        $this->reorderMode = false;
        $this->dispatch('notify', type: 'success', text: 'فرم با موفقیت ذخیره شد.');
    }

    public function deleteForm()
    {
        if ($this->activeFormId && $this->activeFormId !== 'new' && CheckoutForm::count() > 1) {
            CheckoutForm::find($this->activeFormId)->delete();
            $this->forms = CheckoutForm::latest()->get();
            $this->selectForm($this->forms->first()->id ?? null);
            $this->dispatch('notify', type: 'success', text: 'فرم با موفقیت حذف شد.');
        } else {
            $this->dispatch('notify', type: 'error', text: 'امکان حذف این فرم وجود ندارد.');
        }
    }

    public function getGroupedSchema(): array
    {
        $fields = collect($this->schema['fields'] ?? []);
        $groups = collect($this->schema['groups'] ?? []);

        // Return a simple indexed array, preserving the order from the main schema array.
        // This is more stable for Livewire and Alpine to track.
        return $groups->map(function($group) use ($fields) {
            if (empty($group['id'])) return null;

            return [
                'id' => $group['id'],
                'name' => $group['name'],
                'fields' => $fields->where('group', $group['id'])->values()->all()
            ];
        })->filter()->values()->all();
    }

    protected function regenerateGroupsFromFields()
    {
        $groups = collect($this->schema['fields'])
            ->pluck('group')
            ->unique()
            ->filter()
            ->map(function($groupName) {
                if (Str::startsWith($groupName, 'group_')) {
                    $existing = collect($this->schema['groups'])->firstWhere('id', $groupName);
                    return $existing ?? ['id' => $groupName, 'name' => 'گروه بازیابی شده'];
                }
                return ['id' => 'group_' . Str::random(4), 'name' => $groupName];
            })
            ->keyBy('id')
            ->values();

        $this->schema['groups'] = $groups->all();

        $groupMapping = $groups->pluck('id', 'name');
        $this->schema['fields'] = collect($this->schema['fields'])->map(function($field) use ($groupMapping) {
            if (isset($field['group']) && isset($groupMapping[$field['group']])) {
                $field['group'] = $groupMapping[$field['group']];
            }
            return $field;
        })->all();
    }

    public function render()
    {
        return view('market::livewire.admin.checkout-form-manager', [
            'groupedSchema' => $this->getGroupedSchema(),
            'systemFields' => CheckoutForm::getSystemFields(),
        ])->layout('layouts.user');
    }
}
