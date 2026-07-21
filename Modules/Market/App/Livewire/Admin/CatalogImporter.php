<?php

namespace Modules\Market\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Modules\Market\Entities\Brand;
use Modules\Market\Entities\Category;
use Modules\Market\Entities\DisplayCategory;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\App\Services\ProductService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use SplFileObject;
use Exception;

class CatalogImporter extends Component
{
    use WithFileUploads;

    // File Upload
    public $file;
    public $filePath;

    // UI States
    public $isParsed = false;
    public $importing = false;
    public $isFinished = false;
    public $updateExisting = true; // Default to update existing
    public $defaultStatus = 'active'; // Default status is active

    // Mapping & Preview
    public $hasHeaders = true;
    public $fieldMapping = [];
    public $csvHeaders = [];
    public $previewData = [];
    public $availableFields = [];

    // Progress
    public $totalRows = 0;
    public $processedRows = 0;
    public $importCount = 0;
    public $updateCount = 0;
    public $importErrors = [];

    const CHUNK_SIZE = 50;

    protected $rules = [
        'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB Max
    ];

    protected $messages = [
        'file.required' => 'لطفاً یک فایل را انتخاب کنید.',
        'file.mimes' => 'فرمت فایل باید CSV یا TXT باشد.',
        'file.max' => 'حجم فایل نمی‌تواند بیشتر از ۱۰ مگابایت باشد.',
    ];

    // Caches to prevent N+1 queries during import
    protected $brandCache = [];
    protected $categoryCache = [];
    protected $displayCategoryCache = [];

    public function mount()
    {
        $this->loadAvailableFields();
    }

    public function updatedFile()
    {
        $this->validate();
        $this->filePath = $this->file->store('imports');
        $this->parseCsv();
        $this->isParsed = true;
    }

    public function updatedHasHeaders()
    {
        if ($this->filePath) {
            $this->parseCsv();
        }
    }

    protected function loadAvailableFields()
    {
        $this->availableFields = [
            'title' => 'نام محصول (اجباری)',
            'brand' => 'نام برند (اجباری)',
            'category' => 'مسیر دسته‌بندی اصلی (اجباری) - مثال: لوازم خانگی > آشپزخانه',
            'display_category' => 'مسیر دسته‌بندی فروشگاه (مجزا) - مثال: پرفروش‌ها > تخفیف‌دارها',
            'crm_code' => 'شناسه هوشمند (CRM)',
            'barcode' => 'بارکد / کد داخلی',
            'gtin' => 'شناسه جهانی (GTIN/UPC/EAN)',
            'slug' => 'پیوند یکتا (Slug)',
            'short_description' => 'توضیحات کوتاه',
            'description' => 'توضیحات کامل',
            'status' => 'وضعیت (active / draft / archived)',
            'weight' => 'وزن (کیلوگرم)',
            'length' => 'طول (سانتی‌متر)',
            'width' => 'عرض (سانتی‌متر)',
            'height' => 'ارتفاع (سانتی‌متر)',
            'enable_reviews' => 'امکان ثبت نظر (1 یا 0)',
            'enable_questions' => 'امکان ثبت سوال (1 یا 0)',
            'single_sell' => 'امکان تک فروشی (1 یا 0)',
        ];
    }

    public function parseCsv()
    {
        try {
            $path = storage_path('app/' . $this->filePath);
            $file = new SplFileObject($path, 'r');
            $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

            // Headers
            $file->rewind();
            $this->csvHeaders = $file->fgetcsv() ?: [];
            if (!$this->hasHeaders) {
                $file->rewind();
                $firstRow = $file->fgetcsv() ?: [];
                $this->csvHeaders = array_map(fn($i) => "ستون " . ($i + 1), array_keys($firstRow));
            }

            // Clean headers (remove BOM or spaces)
            $this->csvHeaders = array_map(function($header) {
                return trim(preg_replace('/[\x{FEFF}\x{200B}]/u', '', $header));
            }, $this->csvHeaders);

            // Auto-mapping
            foreach ($this->csvHeaders as $index => $header) {
                $this->fieldMapping[$index] = $this->guessField($header);
            }

            // Preview
            $file->rewind();
            if ($this->hasHeaders) $file->fgetcsv(); // Skip header
            $this->previewData = [];
            for ($i = 0; $i < 5; $i++) {
                if ($file->eof()) break;
                $row = $file->fgetcsv();
                if ($row) {
                    $this->previewData[] = $row;
                }
            }

            // Total Rows
            $file->rewind();
            $this->totalRows = 0;
            while (!$file->eof()) {
                if ($file->fgetcsv()) {
                    $this->totalRows++;
                }
            }
            if ($this->hasHeaders) {
                $this->totalRows--;
            }

            if ($this->totalRows > 5000) {
                $this->dispatch('notify', type: 'warning', text: 'فایل حاوی بیش از ۵۰۰۰ ردیف است. جهت عملکرد بهتر و جلوگیری از اتمام زمان سرور، توصیه می‌شود فایل را به بخش‌های کوچک‌تر تقسیم کنید.');
            }

        } catch (Exception $e) {
            $this->dispatch('notify', type: 'error', text: 'خطا در خواندن فایل CSV: ' . $e->getMessage());
            $this->resetState();
        }
    }

    protected function guessField($header)
    {
        $header = trim(mb_strtolower($header, 'UTF-8'));
        $commonMappings = [
            'عنوان' => 'title',
            'نام محصول' => 'title',
            'title' => 'title',
            'name' => 'title',
            
            'برند' => 'brand',
            'brand' => 'brand',
            
            'دسته' => 'category',
            'دسته بندی' => 'category',
            'دسته بندی اصلی' => 'category',
            'category' => 'category',
            
            'دسته بندی مجزا' => 'display_category',
            'دسته بندی فروشگاه' => 'display_category',
            'display_category' => 'display_category',
            
            'شناسه هوشمند' => 'crm_code',
            'crm_code' => 'crm_code',
            'sku' => 'crm_code',
            
            'بارکد' => 'barcode',
            'کد داخلی' => 'barcode',
            'barcode' => 'barcode',
            
            'gtin' => 'gtin',
            'upc' => 'gtin',
            'ean' => 'gtin',
            'شناسه جهانی' => 'gtin',
            
            'slug' => 'slug',
            'پیوند یکتا' => 'slug',
            
            'توضیح کوتاه' => 'short_description',
            'توضیحات کوتاه' => 'short_description',
            'short_description' => 'short_description',
            
            'توضیحات' => 'description',
            'توضیح کامل' => 'description',
            'description' => 'description',
            
            'وضعیت' => 'status',
            'status' => 'status',
            
            'وزن' => 'weight',
            'weight' => 'weight',
            
            'طول' => 'length',
            'length' => 'length',
            
            'عرض' => 'width',
            'width' => 'width',
            
            'ارتفاع' => 'height',
            'height' => 'height',
            
            'نظرات' => 'enable_reviews',
            'enable_reviews' => 'enable_reviews',
            
            'سوالات' => 'enable_questions',
            'enable_questions' => 'enable_questions',
            
            'تک فروشی' => 'single_sell',
            'single_sell' => 'single_sell',
        ];

        return $commonMappings[$header] ?? '';
    }

    public function startImport()
    {
        // Require title mapping
        if (!in_array('title', $this->fieldMapping)) {
            $this->dispatch('notify', type: 'error', text: 'نگاشت فیلد "نام محصول" الزامی است.');
            return;
        }

        $this->importing = true;
    }

    public function processChunk()
    {
        if (!$this->importing || $this->isFinished) return;

        try {
            $path = storage_path('app/' . $this->filePath);
            $file = new SplFileObject($path, 'r');
            $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
            ini_set('auto_detect_line_endings', TRUE);

            $startPosition = $this->processedRows + ($this->hasHeaders ? 1 : 0);
            if ($startPosition > 0) {
                $file->seek($startPosition);
            } else if ($this->hasHeaders) {
                $file->fgetcsv();
            }

            $processedInThisChunk = 0;
            while (!$file->eof() && $processedInThisChunk < self::CHUNK_SIZE) {
                $row = $file->fgetcsv();
                if ($row === false || empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) {
                    if (!$file->eof()) {
                        $this->processedRows++;
                    }
                    $processedInThisChunk++;
                    continue;
                }
                $this->processRow($row, $file->key() + 1);
                $this->processedRows++;
                $processedInThisChunk++;
            }

            if ($this->processedRows >= $this->totalRows) {
                $this->finishImport();
            }
        } catch (Exception $e) {
            $this->finishImportWithError($e->getMessage());
        }
    }

    protected function processRow($row, $rowNumber)
    {
        // Extract raw data from mapped fields
        $rowData = [];
        foreach ($this->fieldMapping as $index => $fieldKey) {
            if (empty($fieldKey) || !isset($row[$index])) continue;
            $rowData[$fieldKey] = trim($row[$index]);
        }

        if (empty($rowData['title'])) {
            $this->importErrors[] = ['row' => $rowNumber, 'error' => 'نام محصول (Title) خالی است.'];
            return;
        }

        try {
            DB::beginTransaction();

            // 1. Resolve or Create Brand
            $brandName = $rowData['brand'] ?? '';
            $brandId = null;
            if (!empty($brandName)) {
                $brand = $this->resolveOrCreateBrand($brandName);
                $brandId = $brand->id;
            }

            // 2. Resolve or Create Category (and link brand if new last child)
            $categoryPath = $rowData['category'] ?? '';
            $categoryId = null;
            if (!empty($categoryPath)) {
                $category = $this->resolveOrCreateCategory($categoryPath, $brandId);
                $categoryId = $category->id;
            }

            // 3. Find Existing Product
            $existingProduct = null;
            $crmCode = $this->sanitizeCodeField($rowData['crm_code'] ?? null);
            $barcode = $this->sanitizeCodeField($rowData['barcode'] ?? null);
            $gtin = $this->sanitizeCodeField($rowData['gtin'] ?? null);

            if ($crmCode || $barcode || $gtin) {
                $query = MasterProduct::query();
                $query->where(function ($q) use ($crmCode, $barcode, $gtin) {
                    if ($crmCode) $q->orWhere('crm_code', $crmCode);
                    if ($barcode) $q->orWhere('barcode', $barcode);
                    if ($gtin) $q->orWhere('gtin', $gtin);
                });
                $existingProduct = $query->first();
            }

            // Validation checks if creating a new product
            if (!$existingProduct) {
                if (empty($categoryId)) {
                    $this->importErrors[] = ['row' => $rowNumber, 'error' => 'تعیین دسته‌بندی برای ایجاد محصول جدید الزامی است.'];
                    DB::rollBack();
                    return;
                }

                if (empty($brandId)) {
                    $this->importErrors[] = ['row' => $rowNumber, 'error' => 'تعیین برند برای ایجاد محصول جدید الزامی است.'];
                    DB::rollBack();
                    return;
                }
            }

            if ($existingProduct && !$this->updateExisting) {
                $this->importErrors[] = [
                    'row' => $rowNumber,
                    'error' => "محصولی با این شناسه‌ها قبلاً در دیتابیس ثبت شده است (کد هوشمند: {$existingProduct->crm_code}). جهت بروزرسانی تیک بروزرسانی محصولات را فعال کنید."
                ];
                DB::rollBack();
                return;
            }

            // Validate uniqueness constraints if creating or modifying fields
            if ($barcode) {
                $dupQuery = MasterProduct::where('barcode', $barcode);
                if ($existingProduct) $dupQuery->where('id', '!=', $existingProduct->id);
                if ($dupQuery->exists()) {
                    $this->importErrors[] = ['row' => $rowNumber, 'error' => "بارکد '{$barcode}' تکراری است و قبلاً برای کالای دیگری ثبت شده است."];
                    DB::rollBack();
                    return;
                }
            }

            if ($gtin) {
                $dupQuery = MasterProduct::where('gtin', $gtin);
                if ($existingProduct) $dupQuery->where('id', '!=', $existingProduct->id);
                if ($dupQuery->exists()) {
                    $this->importErrors[] = ['row' => $rowNumber, 'error' => "شناسه جهانی GTIN '{$gtin}' تکراری است و قبلاً برای کالای دیگری ثبت شده است."];
                    DB::rollBack();
                    return;
                }
            }

            // 4. Prepare data for model saving
            $fillData = [];

            if ($existingProduct) {
                // In update mode, only update keys that have new values in the CSV (preserve existing if empty)
                if (isset($rowData['title']) && trim($rowData['title']) !== '') {
                    $fillData['title'] = $rowData['title'];
                }
                if (isset($rowData['short_description']) && trim($rowData['short_description']) !== '') {
                    $fillData['short_description'] = $rowData['short_description'];
                }
                if (isset($rowData['description']) && trim($rowData['description']) !== '') {
                    $fillData['description'] = $rowData['description'];
                }
                if (isset($rowData['status']) && in_array($rowData['status'], ['active', 'draft', 'archived'])) {
                    $fillData['status'] = $rowData['status'];
                }
                if (isset($rowData['weight']) && is_numeric($rowData['weight'])) {
                    $fillData['weight'] = (float)$rowData['weight'];
                }
                if (isset($rowData['length']) && is_numeric($rowData['length'])) {
                    $fillData['length'] = (float)$rowData['length'];
                }
                if (isset($rowData['width']) && is_numeric($rowData['width'])) {
                    $fillData['width'] = (float)$rowData['width'];
                }
                if (isset($rowData['height']) && is_numeric($rowData['height'])) {
                    $fillData['height'] = (float)$rowData['height'];
                }
                if (isset($rowData['enable_reviews']) && $rowData['enable_reviews'] !== '') {
                    $fillData['enable_reviews'] = (bool)$rowData['enable_reviews'];
                }
                if (isset($rowData['enable_questions']) && $rowData['enable_questions'] !== '') {
                    $fillData['enable_questions'] = (bool)$rowData['enable_questions'];
                }
                if (isset($rowData['single_sell']) && $rowData['single_sell'] !== '') {
                    $fillData['single_sell'] = (bool)$rowData['single_sell'];
                }
                if ($brandId) {
                    $fillData['brand_id'] = $brandId;
                }
                if ($categoryId) {
                    $fillData['category_id'] = $categoryId;
                }
                if ($barcode !== null && $barcode !== '') {
                    $fillData['barcode'] = $barcode;
                }
                if ($gtin !== null && $gtin !== '') {
                    $fillData['gtin'] = $gtin;
                }

                // Slug is NOT updated: "slug هم امکان آپدیتش نباید باشه"

                $existingProduct->update($fillData);
                $product = $existingProduct;
                $this->updateCount++;
            } else {
                // In create mode, fill all fields and assign defaults/slugs
                $status = $rowData['status'] ?? $this->defaultStatus;
                if (!in_array($status, ['active', 'draft', 'archived'])) {
                    $status = $this->defaultStatus;
                }

                $fillData = [
                    'title' => $rowData['title'],
                    'short_description' => $rowData['short_description'] ?? null,
                    'description' => $rowData['description'] ?? null,
                    'status' => $status,
                    'weight' => isset($rowData['weight']) && is_numeric($rowData['weight']) ? (float)$rowData['weight'] : null,
                    'length' => isset($rowData['length']) && is_numeric($rowData['length']) ? (float)$rowData['length'] : null,
                    'width' => isset($rowData['width']) && is_numeric($rowData['width']) ? (float)$rowData['width'] : null,
                    'height' => isset($rowData['height']) && is_numeric($rowData['height']) ? (float)$rowData['height'] : null,
                    'enable_reviews' => isset($rowData['enable_reviews']) && $rowData['enable_reviews'] !== '' ? (bool)$rowData['enable_reviews'] : true,
                    'enable_questions' => isset($rowData['enable_questions']) && $rowData['enable_questions'] !== '' ? (bool)$rowData['enable_questions'] : true,
                    'single_sell' => isset($rowData['single_sell']) && $rowData['single_sell'] !== '' ? (bool)$rowData['single_sell'] : false,
                    'brand_id' => $brandId,
                    'category_id' => $categoryId,
                    'barcode' => empty($barcode) ? null : $barcode,
                    'gtin' => empty($gtin) ? null : $gtin,
                ];

                // Generate slug only for creation
                $slugInput = !empty(trim($rowData['slug'] ?? '')) ? $rowData['slug'] : $rowData['title'];
                $slug = $this->makeSlugUnique($slugInput, null);
                if ($slug === 'product' && !empty($rowData['title'])) {
                    $slug = $this->makeSlugUnique($rowData['title'], null);
                }
                $fillData['slug'] = $slug;

                // Generate CRM Code if not provided
                if (empty($crmCode)) {
                    $fillData['crm_code'] = (new ProductService())->generateCrmCode($brandId, $categoryId);
                } else {
                    // Check custom crm_code uniqueness
                    if (MasterProduct::where('crm_code', $crmCode)->exists()) {
                        $this->importErrors[] = ['row' => $rowNumber, 'error' => "کد هوشمند CRM '{$crmCode}' تکراری است."];
                        DB::rollBack();
                        return;
                    }
                    $fillData['crm_code'] = $crmCode;
                }

                $product = MasterProduct::create($fillData);
                $this->importCount++;
            }

            // 5. Sync Display Categories (and all parent categories in their path)
            $displayCategoryPath = $rowData['display_category'] ?? '';
            if (!empty($displayCategoryPath)) {
                $displayCategory = $this->resolveOrCreateDisplayCategory($displayCategoryPath);
                if ($displayCategory) {
                    $displayCategoryIds = $this->getAllDisplayCategoryIdsInPath($displayCategory);
                    $product->displayCategories()->sync($displayCategoryIds);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->importErrors[] = ['row' => $rowNumber, 'error' => 'خطا در پردازش سطر: ' . $e->getMessage()];
        }
    }

    protected function resolveOrCreateBrand($name)
    {
        $name = trim($name);
        $cacheKey = mb_strtolower($name, 'UTF-8');
        if (isset($this->brandCache[$cacheKey])) {
            return $this->brandCache[$cacheKey];
        }

        $brand = Brand::where('name', 'like', $name)->first();
        if (!$brand) {
            $lastPrefix = Brand::max('code_prefix') ?? 2999;
            $code_prefix = $lastPrefix + 1;

            $slug = Str::slug($name);
            if (empty($slug)) {
                $slug = 'brand-' . Str::random(4);
            }
            $originalSlug = $slug;
            $count = 1;
            while (Brand::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            $brand = Brand::create([
                'name' => $name,
                'slug' => $slug,
                'code_prefix' => $code_prefix,
                'is_active' => true,
            ]);
        }

        $this->brandCache[$cacheKey] = $brand;
        return $brand;
    }

    protected function resolveOrCreateCategory($categoryPath, $brandId = null)
    {
        $categoryPath = trim($categoryPath);
        $cacheKey = mb_strtolower($categoryPath, 'UTF-8') . '-' . ($brandId ?? '0');
        if (isset($this->categoryCache[$cacheKey])) {
            return $this->categoryCache[$cacheKey];
        }

        $parts = array_filter(array_map('trim', explode('>', $categoryPath)));
        $parentId = null;
        $category = null;

        foreach ($parts as $partName) {
            $category = Category::where('name', 'like', $partName)
                ->where('parent_id', $parentId)
                ->first();

            if (!$category) {
                $lastOffset = Category::max('code_offset') ?? 0;
                $code_offset = $lastOffset + 100000;

                $slug = Str::slug($partName);
                if (empty($slug)) {
                    $slug = 'category-' . Str::random(4);
                }
                $originalSlug = $slug;
                $count = 1;
                while (Category::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }

                $category = Category::create([
                    'name' => $partName,
                    'slug' => $slug,
                    'parent_id' => $parentId,
                    'brand_id' => $brandId,
                    'code_offset' => $code_offset,
                    'is_active' => true,
                ]);
            } else {
                if ($brandId && !$category->brand_id) {
                    $category->update(['brand_id' => $brandId]);
                }
            }

            $parentId = $category->id;
        }

        $this->categoryCache[$cacheKey] = $category;
        return $category;
    }

    protected function resolveOrCreateDisplayCategory($displayCategoryPath)
    {
        $displayCategoryPath = trim($displayCategoryPath);
        if (isset($this->displayCategoryCache[$displayCategoryPath])) {
            return $this->displayCategoryCache[$displayCategoryPath];
        }

        $parts = array_filter(array_map('trim', explode('>', $displayCategoryPath)));
        $parentId = null;
        $displayCategory = null;

        foreach ($parts as $partName) {
            $displayCategory = DisplayCategory::where('name', 'like', $partName)
                ->where('parent_id', $parentId)
                ->first();

            if (!$displayCategory) {
                $slug = Str::slug($partName);
                if (empty($slug)) {
                    $slug = 'dcat-' . Str::random(4);
                }
                $originalSlug = $slug;
                $count = 1;
                while (DisplayCategory::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }

                $displayCategory = DisplayCategory::create([
                    'name' => $partName,
                    'slug' => $slug,
                    'parent_id' => $parentId,
                    'is_active' => true,
                ]);
            }

            $parentId = $displayCategory->id;
        }

        $this->displayCategoryCache[$displayCategoryPath] = $displayCategory;
        return $displayCategory;
    }

    protected function getAllDisplayCategoryIdsInPath($displayCategory)
    {
        $ids = [];
        $current = $displayCategory;
        while ($current) {
            $ids[] = $current->id;
            $current = $current->parent_id ? DisplayCategory::find($current->parent_id) : null;
        }
        return $ids;
    }

    protected function makeSlugUnique($slug, $ignoreId = null)
    {
        $slug = mb_strtolower($slug, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9\x{0600}-\x{06FF}\s-]/u', '', $slug);
        $slug = trim($slug);
        $slug = preg_replace('/\s+/u', '-', $slug);
        $slug = preg_replace('/-+/u', '-', $slug);

        if (empty($slug)) {
            $slug = 'product';
        }

        $originalSlug = $slug;
        $count = 1;

        while (true) {
            $query = DB::table('market_master_products')->where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }

            if (!$query->exists()) {
                break;
            }

            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="catalog_import_template.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $columns = [
            'title',
            'brand',
            'category',
            'display_category',
            'crm_code',
            'barcode',
            'gtin',
            'slug',
            'short_description',
            'description',
            'status',
            'weight',
            'length',
            'width',
            'height',
            'enable_reviews',
            'enable_questions',
            'single_sell'
        ];

        $exampleRow = [
            'گوشی موبایل اپل مدل iPhone 15 Pro Max',
            'اپل',
            'کالای دیجیتال > موبایل > گوشی موبایل',
            'موبایل و لوازم جانبی > گوشی اپل',
            '',
            "'123456789012",
            "'01234567890123",
            'apple-iphone-15-pro-max',
            'توضیح کوتاه محصول',
            'توضیحات کامل محصول',
            'active',
            '0.22',
            '16',
            '7.6',
            '0.8',
            '1',
            '1',
            '0'
        ];

        $callback = function() use ($columns, $exampleRow) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM for Persian Excel support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);
            fputcsv($file, $exampleRow);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function finishImport()
    {
        $this->importing = false;
        $this->isFinished = true;
        if ($this->filePath) {
            Storage::delete($this->filePath);
            $this->filePath = null;
        }
    }

    public function finishImportWithError($message)
    {
        $this->importing = false;
        $this->isFinished = true;
        $this->dispatch('notify', type: 'error', text: 'خطا در پردازش ایمپورت: ' . $message);
    }

    public function resetState()
    {
        if ($this->filePath) {
            Storage::delete($this->filePath);
        }
        $this->file = null;
        $this->filePath = null;
        $this->isParsed = false;
        $this->importing = false;
        $this->isFinished = false;
        $this->processedRows = 0;
        $this->totalRows = 0;
        $this->importCount = 0;
        $this->updateCount = 0;
        $this->importErrors = [];
        $this->fieldMapping = [];
    }

    protected function sanitizeCodeField($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim($value);
        // Remove leading/trailing single or double quotes
        $value = preg_replace('/^[\'"]|[\'"]$/', '', $value);
        $value = trim($value);

        // Convert scientific notation (like 1.23E+12) back to full string representation
        if (is_numeric($value) && preg_match('/^[+-]?[0-9]*\.?[0-9]+[eE][+-]?[0-9]+$/', $value)) {
            $value = sprintf("%.0f", (float)$value);
        }

        return $value;
    }

    public function render()
    {
        return view('market::livewire.admin.catalog-importer');
    }
}
