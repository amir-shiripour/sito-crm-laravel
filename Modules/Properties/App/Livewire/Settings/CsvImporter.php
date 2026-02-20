<?php

namespace Modules\Properties\App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertyStatus;
use Modules\Properties\Entities\PropertyCategory;

class CsvImporter extends Component
{
    use WithFileUploads;

    public $file;
    public $headers = [];
    public $data = [];
    public $mapping = [];
    public $isParsed = false;
    public $importing = false;
    public $importCount = 0;

    // فیلدهای قابل ایمپورت در مدل ملک
    public $fields = [
        'title' => 'عنوان',
        'description' => 'توضیحات',
        'price' => 'قیمت',
        'area' => 'متراژ',
        'address' => 'آدرس',
        'bedrooms' => 'تعداد خواب',
        'bathrooms' => 'تعداد حمام',
        'year_built' => 'سال ساخت',
        'latitude' => 'عرض جغرافیایی',
        'longitude' => 'طول جغرافیایی',
        'reference_code' => 'کد مرجع',
    ];

    public function updatedFile()
    {
        $this->validate([
            'file' => 'required|mimes:csv,txt|max:10240', // محدودیت ۱۰ مگابایت
        ]);

        $this->parseCsv();
    }

    public function parseCsv()
    {
        $path = $this->file->getRealPath();
        $file = fopen($path, 'r');

        // خواندن هدرها (ردیف اول)
        $this->headers = fgetcsv($file);

        // خواندن چند ردیف اول برای پیش‌نمایش
        $rowLimit = 5;
        $rowCount = 0;
        $this->data = [];

        while (($row = fgetcsv($file)) !== false && $rowCount < $rowLimit) {
            // اطمینان از همخوانی تعداد ستون‌ها با هدر
            if (count($row) === count($this->headers)) {
                $this->data[] = array_combine($this->headers, $row);
                $rowCount++;
            }
        }

        fclose($file);

        // پیشنهاد mapping اولیه بر اساس نام ستون‌ها
        foreach ($this->fields as $fieldKey => $fieldLabel) {
            $this->mapping[$fieldKey] = '';
            foreach ($this->headers as $header) {
                if (stripos($header, $fieldKey) !== false || stripos($header, $fieldLabel) !== false) {
                    $this->mapping[$fieldKey] = $header;
                    break;
                }
            }
        }

        $this->isParsed = true;
    }

    public function import()
    {
        $this->validate([
            'mapping.title' => 'required', // حداقل عنوان الزامی است
        ]);

        $this->importing = true;

        $path = $this->file->getRealPath();
        $file = fopen($path, 'r');
        fgetcsv($file); // رد کردن هدر

        $user = Auth::user();
        $teamId = $user->currentTeam->id;

        // دریافت وضعیت و دسته‌بندی پیش‌فرض (در صورت نیاز می‌توانید این‌ها را هم از کاربر بگیرید)
        $defaultStatus = PropertyStatus::where('team_id', $teamId)->first();
        $defaultCategory = PropertyCategory::where('team_id', $teamId)->first();

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) !== count($this->headers)) {
                continue;
            }

            $rowData = array_combine($this->headers, $row);

            $propertyData = [
                'user_id' => $user->id,
                'team_id' => $teamId,
                'status_id' => $defaultStatus?->id,
                'category_id' => $defaultCategory?->id,
            ];

            foreach ($this->mapping as $field => $csvHeader) {
                if (!empty($csvHeader) && isset($rowData[$csvHeader])) {
                    $value = $rowData[$csvHeader];

                    // تبدیل داده‌ها در صورت نیاز (مثلاً اعداد)
                    if (in_array($field, ['price', 'area', 'bedrooms', 'bathrooms', 'year_built'])) {
                        $value = (float) preg_replace('/[^0-9.]/', '', $value);
                    }

                    $propertyData[$field] = $value;
                }
            }

            // ایجاد ملک
            Property::create($propertyData);
            $this->importCount++;
        }

        fclose($file);

        $this->importing = false;
        $this->reset(['file', 'isParsed', 'headers', 'data', 'mapping']);

        session()->flash('message', "تعداد {$this->importCount} ملک با موفقیت ایمپورت شد.");
        $this->importCount = 0;
    }

    public function render()
    {
        return view('properties::user.settings.csv-importer-component');
    }
}
