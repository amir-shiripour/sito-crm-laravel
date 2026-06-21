<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Market\Entities\Brand;
use Illuminate\Support\Str;

class BrandManager extends Component
{
    use WithPagination, \Livewire\WithFileUploads;

    public $brand_id, $name, $slug, $code_prefix, $is_active = true;
    public $logo, $existing_logo;
    public $isFormOpen = false;

    public function openForm(?int $id = null)
    {
        $this->resetValidation();
        if ($id) {
            $brand = Brand::findOrFail($id);
            $this->brand_id = $brand->id;
            $this->name = $brand->name;
            $this->slug = $brand->slug;
            $this->code_prefix = $brand->code_prefix;
            $this->existing_logo = $brand->logo;
            $this->is_active = (bool) $brand->is_active;
        } else {
            $this->reset(['brand_id', 'name', 'slug', 'logo', 'existing_logo']);
            $this->is_active = true;
            // تولید خودکار کد برند (از 3000 شروع می‌شود و یکی یکی بالا می‌رود)
            $lastPrefix = Brand::max('code_prefix') ?? 2999;
            $this->code_prefix = $lastPrefix + 1;
        }
        $this->isFormOpen = true;
    }

    public function closeForm()
    {
        $this->isFormOpen = false;
        $this->reset(['logo']);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code_prefix' => 'required|integer|unique:market_brands,code_prefix,' . $this->brand_id,
            'slug' => 'nullable|string|max:255|unique:market_brands,slug,' . $this->brand_id,
            'logo' => 'nullable|image|max:2048',
        ]);

        $brand = Brand::updateOrCreate(
            ['id' => $this->brand_id],
            [
                'name' => $this->name,
                'slug' => $this->slug ?: Str::slug($this->name),
                'code_prefix' => $this->code_prefix,
                'is_active' => $this->is_active,
            ]
        );

        if ($this->logo) {
            if ($brand->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($brand->logo);
            }
            $path = $this->logo->store('brands', 'public');
            $brand->update(['logo' => $path]);
        }

        $this->dispatch('notify', type: 'success', text: 'برند با موفقیت ذخیره شد.');
        $this->closeForm();
    }

    public function delete($id)
    {
        Brand::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', text: 'برند حذف شد.');
    }

    public function render()
    {
        return view('market::livewire.admin.brand-manager', [
            'brands' => Brand::latest()->paginate(10)
        ]);
    }
}
