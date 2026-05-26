<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Modules\Market\App\Models\CheckoutForm;
use Modules\Market\Entities\MasterProduct; // Corrected import
use Modules\Market\Entities\Category;

class AdminOrderForm extends Component
{
    public $forms;
    public $selectedFormId;
    public CheckoutForm $editingForm;

    protected function rules()
    {
        $rules = [
            'editingForm.name' => 'required|string|max:255',
            'editingForm.key' => 'required|string|max:255|unique:checkout_forms,key,' . $this->editingForm->id,
            'editingForm.is_active' => 'boolean',
            'editingForm.product_id' => 'nullable|exists:market_master_products,id', // Corrected table name
            'editingForm.category_id' => 'nullable|exists:market_categories,id',
            'editingForm.schema' => 'required|array',
        ];

        return $rules;
    }

    public function mount()
    {
        $this->forms = CheckoutForm::all();
        $this->editingForm = new CheckoutForm(['is_active' => true, 'schema' => ['fields' => []]]);
    }

    public function selectForm($formId = null)
    {
        $this->selectedFormId = $formId;
        $this->editingForm = $formId ? CheckoutForm::find($formId) : new CheckoutForm(['is_active' => true, 'schema' => ['fields' => []]]);
    }

    public function save()
    {
        $this->validate();

        $this->editingForm->schema = CheckoutForm::normalizeSchema($this->editingForm->schema);

        $this->editingForm->save();

        $this->forms = CheckoutForm::all();
        $this->selectForm($this->editingForm->id);

        $this->dispatch('notify', type: 'success', text: 'فرم با موفقیت ذخیره شد.');
    }

    public function render()
    {
        $products = MasterProduct::pluck('title', 'id'); // Corrected Model and column
        $categories = Category::pluck('name', 'id');

        return view('market::livewire.admin.admin-order-form', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
