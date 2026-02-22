<?php

namespace Modules\Properties\App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertyStatus;
use Modules\Properties\Entities\PropertyCategory;
use Modules\Properties\Entities\PropertyBuilding;
use Modules\Properties\Entities\PropertyOwner;
use Modules\Properties\Entities\PropertyAttribute;
use Modules\Properties\Entities\PropertyAttributeValue;
use Modules\Properties\Entities\PropertySetting;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Str;
use App\Models\User;

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
    public $totalRows = 0;
    public $processedRows = 0;
    public $failedRows = 0;
    public $importErrors = [];
    public $autoGenerateCode = false;
    public $importFinished = false;

    // لیست‌های انتخاب برای مقادیر ثابت (به صورت آرایه)
    public $statuses = [];
    public $categories = [];
    public $buildings = [];
    public $agents = [];

    // فیلدهای قابل ایمپورت در مدل ملک
    public $fields = [
        'title' => 'عنوان',
        'description' => 'توضیحات',
        'listing_type' => 'نوع آگهی (sale, rent, presale)',
        'property_type' => 'نوع ملک (apartment, villa, land, office)',
        'price' => 'قیمت کل (فروش/پیش‌فروش)',
        'min_price' => 'حداقل قیمت',
        'deposit_price' => 'قیمت رهن',
        'rent_price' => 'قیمت اجاره',
        'advance_price' => 'قیمت پیش‌پرداخت',
        'address' => 'آدرس',
        'latitude' => 'عرض جغرافیایی',
        'longitude' => 'طول جغرافیایی',
        'code' => 'کد ملک',
        'delivery_date' => 'تاریخ تحویل (YYYY/MM/DD)',
        'registered_at' => 'تاریخ ثبت (YYYY/MM/DD)',
        'document_type' => 'نوع سند',
        'usage_type' => 'نوع کاربری (residential, commercial, ...)',
        'is_special' => 'ویژه بودن (0/1)',
        'publication_status' => 'وضعیت انتشار', // اضافه شد
        'confidential_notes' => 'یادداشت‌های محرمانه',

        // روابط
        'category_name' => 'نام دسته‌بندی',
        'building_name' => 'نام ساختمان',
        'owner_name' => 'نام مالک (نام و نام خانوادگی)',
        'owner_phone' => 'شماره تماس مالک',
        'status_id' => 'وضعیت ملک',
        'agent_id' => 'مشاور مسئول',
    ];

    public $propertyAttributes = [];

    public function mount()
    {
        $user = Auth::user();

        // بارگذاری ویژگی‌ها
        $attrs = PropertyAttribute::where('is_active', true)->orderBy('sort_order')->get();
        foreach ($attrs as $attr) {
            $this->fields['attr_' . $attr->id] = 'ویژگی: ' . $attr->name;
            $this->propertyAttributes[$attr->id] = $attr->toArray();
        }

        // بارگذاری لیست‌ها و تبدیل به آرایه برای استفاده راحت در Livewire
        $this->statuses = PropertyStatus::where('is_active', true)->orderBy('sort_order')->get()->toArray();
        $this->categories = PropertyCategory::where('user_id', $user->id)->get()->toArray();
        $this->buildings = PropertyBuilding::latest()->get()->toArray();

        $agentRoles = json_decode(PropertySetting::get('agent_roles', '[]'), true);
        $this->agents = User::role($agentRoles)->get(['id', 'name'])->toArray();
    }

    public function updatedFile()
    {
        $this->validate([
            'file' => 'required|mimes:csv,txt|max:10240',
        ]);

        $this->parseCsv();
    }

    public function parseCsv()
    {
        $path = $this->file->getRealPath();
        $file = fopen($path, 'r');

        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file);
        }

        $this->headers = fgetcsv($file);

        $rowLimit = 5;
        $rowCount = 0;
        $this->data = [];

        while (($row = fgetcsv($file)) !== false && $rowCount < $rowLimit) {
            if (count($row) === count($this->headers)) {
                $row = array_map(function($item) {
                    return mb_convert_encoding($item, 'UTF-8', 'auto');
                }, $row);

                $this->data[] = array_combine($this->headers, $row);
                $rowCount++;
            }
        }

        $this->totalRows = 0;
        while (fgetcsv($file) !== false) {
            $this->totalRows++;
        }
        $this->totalRows += $rowCount;

        fclose($file);

        foreach ($this->fields as $fieldKey => $fieldLabel) {
            $this->mapping[$fieldKey] = [
                'source' => 'none',
                'value' => '',
            ];

            foreach ($this->headers as $header) {
                $cleanHeader = trim($header);
                if (stripos($cleanHeader, $fieldKey) !== false || stripos($cleanHeader, str_replace('ویژگی: ', '', $fieldLabel)) !== false) {
                    $this->mapping[$fieldKey]['source'] = 'csv';
                    $this->mapping[$fieldKey]['value'] = $header;
                    break;
                }
            }
        }

        $this->isParsed = true;
        $this->processedRows = 0;
        $this->importCount = 0;
        $this->failedRows = 0;
        $this->importErrors = [];
        $this->importFinished = false;
    }

    public function import()
    {
        if ($this->mapping['title']['source'] === 'none') {
             $this->addError('mapping.title', 'عنوان ملک الزامی است.');
             return;
        }

        $this->importing = true;
        $this->processedRows = 0;
        $this->importCount = 0;
        $this->failedRows = 0;
        $this->importErrors = [];
        $this->importFinished = false;

        $path = $this->file->getRealPath();
        $file = fopen($path, 'r');

        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file);
        }

        fgetcsv($file);

        $user = Auth::user();

        // پیدا کردن وضعیت پیش‌فرض
        $defaultStatus = PropertyStatus::where('is_default', true)->first();
        if (!$defaultStatus) {
            $defaultStatus = PropertyStatus::where('is_active', true)->orderBy('sort_order')->first();
        }

        set_time_limit(300);

        $rowNumber = 1;

        while (($row = fgetcsv($file)) !== false) {
            $rowNumber++;
            $this->processedRows++;

            if (count($row) !== count($this->headers)) {
                $this->failedRows++;
                $this->importErrors[] = "ردیف {$rowNumber}: تعداد ستون‌ها نامعتبر است.";
                continue;
            }

            $row = array_map(function($item) {
                return mb_convert_encoding($item, 'UTF-8', 'auto');
            }, $row);

            $rowData = array_combine($this->headers, $row);

            try {
                $propertyData = [
                    'created_by' => $user->id,
                    'agent_id' => $user->id,
                    'status_id' => $defaultStatus?->id,
                    'publication_status' => 'draft',
                ];

                foreach ($this->mapping as $field => $config) {
                    $source = $config['source'];
                    $sourceValue = $config['value'];
                    $value = null;

                    if ($source === 'csv' && !empty($sourceValue) && isset($rowData[$sourceValue])) {
                        $value = trim($rowData[$sourceValue]);
                    } elseif ($source === 'fixed') {
                        $value = $sourceValue;
                    }

                    if ($value !== null && !str_starts_with($field, 'attr_')) {
                        if (in_array($field, ['price', 'min_price', 'deposit_price', 'rent_price', 'advance_price'])) {
                            $value = (float) preg_replace('/[^0-9.]/', '', $value);
                        } elseif ($field === 'is_special') {
                            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                        } elseif (in_array($field, ['delivery_date', 'registered_at'])) {
                            try {
                                if (preg_match('/^\d{4}\/\d{1,2}\/\d{1,2}$/', $value)) {
                                    $value = Jalalian::fromFormat('Y/m/d', $value)->toCarbon()->format('Y-m-d');
                                }
                            } catch (\Exception $e) {
                                $value = null;
                            }
                        }

                        if (!in_array($field, ['category_name', 'building_name', 'owner_name', 'owner_phone'])) {
                            $propertyData[$field] = $value;
                        }
                    }
                }

                if (empty($propertyData['title'])) {
                    throw new \Exception("عنوان ملک الزامی است.");
                }

                // 1. Category
                if (!empty($this->mapping['category_name']['value'])) {
                     if ($this->mapping['category_name']['source'] === 'csv') {
                         $catName = trim($rowData[$this->mapping['category_name']['value']] ?? '');
                         if (!empty($catName)) {
                             $category = PropertyCategory::firstOrCreate(
                                 ['name' => $catName, 'user_id' => $user->id],
                                 ['slug' => Str::slug($catName), 'color' => '#6366f1']
                             );
                             $propertyData['category_id'] = $category->id;
                         }
                     }
                     elseif ($this->mapping['category_name']['source'] === 'fixed') {
                         $propertyData['category_id'] = $this->mapping['category_name']['value'];
                     }
                }

                // 2. Building
                if (!empty($this->mapping['building_name']['value'])) {
                    if ($this->mapping['building_name']['source'] === 'csv') {
                        $buildingName = trim($rowData[$this->mapping['building_name']['value']] ?? '');
                        if (!empty($buildingName)) {
                            $building = PropertyBuilding::firstOrCreate(
                                ['name' => $buildingName],
                                ['type' => 'residential', 'total_floors' => 1]
                            );
                            $propertyData['building_id'] = $building->id;
                        }
                    } elseif ($this->mapping['building_name']['source'] === 'fixed') {
                        $propertyData['building_id'] = $this->mapping['building_name']['value'];
                    }
                }

                // 3. Owner
                if ($this->mapping['owner_phone']['source'] === 'csv' && !empty($this->mapping['owner_phone']['value'])) {
                    $ownerPhone = trim($rowData[$this->mapping['owner_phone']['value']] ?? '');

                    if (!empty($ownerPhone) && !str_starts_with($ownerPhone, '0')) {
                        $ownerPhone = '0' . $ownerPhone;
                    }

                    if (!empty($ownerPhone)) {
                        $ownerName = 'مالک ناشناس';
                        if ($this->mapping['owner_name']['source'] === 'csv' && !empty($this->mapping['owner_name']['value'])) {
                            $ownerName = trim($rowData[$this->mapping['owner_name']['value']] ?? '') ?: 'مالک ناشناس';
                        } elseif ($this->mapping['owner_name']['source'] === 'fixed') {
                            $ownerName = $this->mapping['owner_name']['value'];
                        }

                        $parts = explode(' ', $ownerName, 2);
                        $firstName = $parts[0];
                        $lastName = isset($parts[1]) ? $parts[1] : '-';

                        $owner = PropertyOwner::firstOrCreate(
                            ['phone' => $ownerPhone],
                            [
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                                'created_by' => $user->id
                            ]
                        );
                        $propertyData['owner_id'] = $owner->id;
                    }
                }

                // 4. Agent
                if (isset($propertyData['agent_id']) && empty($propertyData['agent_id'])) {
                    $propertyData['agent_id'] = $user->id;
                }

                // 5. Code
                $prefix = $this->getPropertyCodePrefix($propertyData['category_id'] ?? null);
                $finalCode = null;

                if (!empty($propertyData['code'])) {
                    $rawCode = $propertyData['code'];
                    if (!str_starts_with($rawCode, $prefix)) {
                        $finalCode = $prefix . $rawCode;
                    } else {
                        $finalCode = $rawCode;
                    }
                }

                if ($finalCode && Property::withTrashed()->where('code', $finalCode)->exists()) {
                    if ($this->autoGenerateCode) {
                        $finalCode = $this->generateUniquePropertyCode($propertyData['category_id'] ?? null);
                    } else {
                        throw new \Exception("کد ملک '{$finalCode}' تکراری است و تولید خودکار غیرفعال می‌باشد.");
                    }
                } elseif (empty($finalCode)) {
                    if ($this->autoGenerateCode) {
                        $finalCode = $this->generateUniquePropertyCode($propertyData['category_id'] ?? null);
                    } else {
                        $finalCode = $this->generateUniquePropertyCode($propertyData['category_id'] ?? null);
                    }
                }

                $propertyData['code'] = $finalCode;

                $property = Property::create($propertyData);

                // 6. Attributes
                foreach ($this->mapping as $field => $config) {
                    if (str_starts_with($field, 'attr_')) {
                        $source = $config['source'];
                        $sourceValue = $config['value'];
                        $value = null;

                        if ($source === 'csv' && !empty($sourceValue) && isset($rowData[$sourceValue])) {
                            $value = trim($rowData[$sourceValue]);
                        } elseif ($source === 'fixed') {
                            $value = $sourceValue;
                        }

                        if (!empty($value)) {
                            $attrId = str_replace('attr_', '', $field);
                            $attr = $this->propertyAttributes[$attrId] ?? null;

                            if ($attr && $attr['type'] === 'checkbox') {
                                if (in_array(strtolower($value), ['1', 'true', 'yes', 'bale', 'بله', 'دارد'])) {
                                    $value = '1';
                                } else {
                                    continue;
                                }
                            }

                            PropertyAttributeValue::create([
                                'property_id' => $property->id,
                                'attribute_id' => $attrId,
                                'value' => $value
                            ]);
                        }
                    }
                }

                $this->importCount++;

            } catch (\Exception $e) {
                $this->failedRows++;
                $this->importErrors[] = "ردیف {$rowNumber}: " . $e->getMessage();
                Log::error("Import Error Row {$rowNumber}: " . $e->getMessage());
            }
        }

        fclose($file);

        $this->importing = false;
        $this->reset(['file', 'isParsed', 'headers', 'data', 'mapping', 'autoGenerateCode']);
        $this->importFinished = true;
    }

    private function getPropertyCodePrefix($categoryId = null): string
    {
        $useCategorySlug = PropertySetting::get('property_code_use_category_slug', 0);
        $prefix = PropertySetting::get('property_code_prefix', 'P');
        $separator = PropertySetting::get('property_code_separator', '-');
        $includeYear = PropertySetting::get('property_code_include_year', 1);

        if ($useCategorySlug && $categoryId) {
            $category = PropertyCategory::find($categoryId);
            if ($category && !empty($category->slug)) {
                $prefix = $category->slug;
            }
        }

        $code = '';
        if ($includeYear) {
            $year = Jalalian::now()->getYear();
            $code .= $year . $separator;
        }

        if (!empty($prefix)) {
            $code .= $prefix . $separator;
        }

        return $code;
    }

    private function generateUniquePropertyCode($categoryId = null): string
    {
        $prefixPart = $this->getPropertyCodePrefix($categoryId);

        $lastProperty = Property::withTrashed()
            ->where('code', 'like', "{$prefixPart}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastProperty) {
            $lastCode = $lastProperty->code;
            $numberPart = str_replace($prefixPart, '', $lastCode);

            if (is_numeric($numberPart)) {
                $newNumber = intval($numberPart) + 1;
            } else {
                $newNumber = 1001;
            }
        } else {
            $newNumber = 1001;
        }

        $newCode = $prefixPart . $newNumber;

        while (Property::withTrashed()->where('code', $newCode)->exists()) {
            $newNumber++;
            $newCode = $prefixPart . $newNumber;
        }

        return $newCode;
    }

    public function render()
    {
        return view('properties::user.settings.csv-importer-component');
    }
}
