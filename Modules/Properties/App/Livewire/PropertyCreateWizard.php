<?php

namespace Modules\Properties\App\Livewire;

use Livewire\Component;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertyCategory;
use Livewire\WithFileUploads;

class PropertyCreateWizard extends Component
{
    use WithFileUploads;

    public $step = 1;
    public $property;
    public $cover_image_file;

    // گزینه‌ها برای dropdowns
    public $listingTypes;
    public $propertyTypes;
    public $categories;

    public function mount()
    {
        $this->property = new Property();

        // مقداردهی اولیه گزینه‌ها
        $this->listingTypes = [
            'sale' => 'فروش',
            'rent' => 'رهن و اجاره',
            'presale' => 'پیش فروش',
        ];

        $this->propertyTypes = [
            'apartment' => 'خانه و آپارتمان',
            'villa' => 'ویلا',
            'land' => 'زمین و باغ',
            'office' => 'اداری و تجاری',
        ];

        $this->categories = PropertyCategory::where('user_id', auth()->id())->pluck('name', 'id');
    }

    public function render()
    {
        return view('properties::livewire.property-create-wizard');
    }

    public function nextStep()
    {
        $this->validateStep($this->step);
        $this->step++;
    }

    public function previousStep()
    {
        $this->step--;
    }

    public function validateStep($step)
    {
        if ($step == 1) {
            $this->validate([
                'property.title' => 'required|string|max:255',
                'property.listing_type' => 'required|in:' . implode(',', array_keys($this->listingTypes)),
                'property.property_type' => 'required|in:' . implode(',', array_keys($this->propertyTypes)),
                'property.category_id' => 'nullable|exists:property_categories,id',
                'property.address' => 'nullable|string',
                'cover_image_file' => 'nullable|image|max:2048', // 2MB Max
            ]);
        }
        // اعتبارسنجی مراحل بعدی اینجا اضافه می‌شود
    }

    public function submit()
    {
        $this->validateStep($this->step); // اعتبارسنجی مرحله آخر

        // ذخیره عکس
        if ($this->cover_image_file) {
            $this->property->cover_image = $this->cover_image_file->store('properties/covers', 'public');
        }

        // تولید کد ملک (اگر خالی بود)
        if (empty($this->property->code)) {
            $this->property->code = 'P' . time(); // یک روش ساده، قابل بهبود است
        }

        $this->property->created_by = auth()->id();
        $this->property->save();

        session()->flash('success', 'ملک با موفقیت ایجاد شد.');
        return redirect()->route('user.properties.index');
    }
}
