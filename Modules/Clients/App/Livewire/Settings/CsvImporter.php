<?php

namespace Modules\Clients\App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Modules\Clients\Entities\ClientForm;
use Modules\Clients\Entities\ClientStatus;
use Modules\Clients\Entities\ClientSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Clients\Entities\Client;
use SplFileObject;
use Exception;

#[Layout('layouts.user')]
class CsvImporter extends Component
{
    use WithFileUploads;

    // File Upload
    public $file;
    public $filePath;

    // UI State
    public $isParsed = false;
    public $importing = false;
    public $isFinished = false;
    public $updateExisting = false;

    // Mapping & Preview
    public $hasHeaders = true;
    public $fieldMapping = [];
    public $csvHeaders = [];
    public $previewData = [];
    public $formFields = [];

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

    public function mount()
    {
        $this->loadFormFields();
    }

    public function updatedFile()
    {
        $this->validate();
        $this->filePath = $this->file->store('imports');
        $this->parseCsv();
        $this->isParsed = true;
    }

    protected function loadFormFields()
    {
        $activeForm = ClientForm::active();
        $fields = [];
        foreach (ClientForm::systemFieldDefaults() as $id => $field) {
            $fields[$id] = $field['label'] . " (سیستمی)";
        }
        if ($activeForm) {
            foreach ($activeForm->schema['fields'] ?? [] as $field) {
                // BUG FIX: Was using $id from parent loop instead of $field['id']
                if (!isset($fields[$field['id']])) {
                    $fields[$field['id']] = $field['label'];
                }
            }
        }
        $this->formFields = $fields;
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

            // Auto-mapping
            foreach ($this->csvHeaders as $index => $header) {
                $this->fieldMapping[$index] = $this->guessField($header);
            }

            // Preview
            $file->rewind();
            if ($this->hasHeaders) $file->fgetcsv(); // Skip header for preview
            $this->previewData = [];
            for ($i = 0; $i < 5; $i++) {
                if ($file->eof()) break;
                $this->previewData[] = $file->fgetcsv();
            }

            // Total Rows
            $file->rewind();
            $this->totalRows = 0;
            while (!$file->eof()) {
                if($file->fgetcsv()){
                    $this->totalRows++;
                }
            }
            if ($this->hasHeaders) $this->totalRows--;


        } catch (Exception $e) {
            $this->dispatch('notify', type: 'error', text: 'فایل CSV نامعتبر است: ' . $e->getMessage());
            $this->resetState();
        }
    }

    protected function guessField($header)
    {
        $header = trim(mb_strtolower($header));
        $commonMappings = [
            'نام' => 'full_name', 'نام و نام خانوادگی' => 'full_name',
            'موبایل' => 'phone', 'تلفن' => 'phone',
            'ایمیل' => 'email', 'کدملی' => 'national_code',
            'کد ملی' => 'national_code', 'شماره پرونده' => 'case_number',
            'یادداشت' => 'notes', 'وضعیت' => 'status_id', 'رمز عبور' => 'password',
        ];

        if (isset($commonMappings[$header])) {
            return $commonMappings[$header];
        }

        // Direct match with field labels (including custom fields)
        $flippedFormFields = array_flip($this->formFields);
        if(isset($flippedFormFields[$header])) {
            return $flippedFormFields[$header];
        }

        return '';
    }

    public function startImport()
    {
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
            if ($startPosition > 0) $file->seek($startPosition);
            else if ($this->hasHeaders) $file->fgetcsv();


            $defaultStatus = ClientStatus::where('is_active', true)->orderBy('sort_order')->first() ?? ClientStatus::first();
            $usernameStrategy = ClientSetting::getValue('username_strategy', 'email_local');

            $processedInThisChunk = 0;
            while (!$file->eof() && $processedInThisChunk < self::CHUNK_SIZE) {
                $row = $file->fgetcsv();
                if ($row === false || empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) {
                    if(!$file->eof()) $this->processedRows++;
                    $processedInThisChunk++;
                    continue;
                }
                $this->processRow($row, $file->key() + 1, $defaultStatus, $usernameStrategy);
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

    protected function processRow($row, $rowNumber, $defaultStatus, $usernameStrategy)
    {
        $clientData = [];
        $metaData = [];
        foreach ($this->fieldMapping as $index => $fieldId) {
            if (empty($fieldId) || !isset($row[$index])) continue;
            $value = trim($row[$index]);
            if ($value !== '') {
                if (ClientForm::isSystemFieldId($fieldId)) $clientData[$fieldId] = $value;
                else $metaData[$fieldId] = $value;
            }
        }

        if (empty($clientData) && empty($metaData)) return;

        if ($this->updateExisting && empty($clientData['phone']) && empty($clientData['email']) && empty($clientData['national_code']) && empty($clientData['case_number'])) {
            $this->importErrors[] = ['row' => $rowNumber, 'error' => 'برای آپدیت، حداقل یکی از فیلدهای شناسه الزامی است.'];
            return;
        }

        if (!empty($clientData['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $clientData['phone']);
            if (!str_starts_with($phone, '0') && strlen($phone) === 10) $phone = '0' . $phone;
            $clientData['phone'] = $phone;
        }

        $existingClient = null;
        if ($this->updateExisting) {
            $query = Client::query()->where(function ($q) use ($clientData) {
                if (!empty($clientData['phone'])) $q->orWhere('phone', $clientData['phone']);
                if (!empty($clientData['email'])) $q->orWhere('email', $clientData['email']);
                if (!empty($clientData['national_code'])) $q->orWhere('national_code', $clientData['national_code']);
                if (!empty($clientData['case_number'])) $q->orWhere('case_number', $clientData['case_number']);
            });
            $existingClient = $query->first();
        }

        if ($existingClient) {
            unset($clientData['username']);
            $clientData['password'] = empty($clientData['password']) ? null : Hash::make($clientData['password'], ['rounds' => 4]);
            if (empty($clientData['password'])) unset($clientData['password']);
            if (!empty($metaData)) $clientData['meta'] = array_merge($existingClient->meta ?? [], $metaData);
            if (!empty($clientData['status_id'])) {
                $status = ClientStatus::where('label', $clientData['status_id'])->orWhere('key', $clientData['status_id'])->first();
                $clientData['status_id'] = $status->id ?? $existingClient->status_id;
            }
            $existingClient->update($clientData);
            $this->updateCount++;
        } else {
            if (empty($clientData['full_name'])) {
                $this->importErrors[] = ['row' => $rowNumber, 'error' => 'فیلد "نام و نام خانوادگی" برای ایجاد کاربر جدید الزامی است.'];
                return;
            }
            if (empty($clientData['username'])) $clientData['username'] = $this->generateUsername($clientData);
            if ($usernameStrategy === 'mobile' && !empty($clientData['username']) && Client::where('username', $clientData['username'])->exists()) {
                $this->importErrors[] = ['row' => $rowNumber, 'error' => "کاربر با نام کاربری {$clientData['username']} قبلاً ثبت شده است."];
                return;
            }
            $clientData['password'] = isset($clientData['password']) ? Hash::make($clientData['password'], ['rounds' => 4]) : Hash::make(Str::random(12), ['rounds' => 4]);
            $clientData['status_id'] = !empty($clientData['status_id']) ? (ClientStatus::where('label', $clientData['status_id'])->orWhere('key', $clientData['status_id'])->first()->id ?? $defaultStatus?->id) : $defaultStatus?->id;
            $clientData['created_by'] = auth()->id();
            $clientData['meta'] = $metaData;
            Client::create($clientData);
            $this->importCount++;
        }
    }

    protected function generateUsername(array $data): string
    {
        $strategy = ClientSetting::getValue('username_strategy', 'email_local');
        $base = '';
        switch ($strategy) {
            case 'mobile': $base = $data['phone'] ?? ''; break;
            case 'national_code': $base = $data['national_code'] ?? ''; break;
            case 'email_local': $base = explode('@', $data['email'] ?? '')[0]; break;
            default: $base = 'user_' . Str::random(6);
        }
        $base = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $base);
        if (empty($base)) $base = 'user_' . Str::random(8);
        if ($strategy === 'mobile') return $base;
        $username = $base;
        $counter = 1;
        while (Client::where('username', $username)->exists()) {
            $username = $base . '_' . $counter++;
        }
        return $username;
    }

    public function finishImport()
    {
        $this->importing = false;
        $this->isFinished = true;
        if ($this->filePath) {
            \Storage::delete($this->filePath);
            $this->filePath = null;
        }
    }

    public function finishImportWithError($message)
    {
        $this->importing = false;
        $this->isFinished = true;
        $this->dispatch('notify', type: 'error', text: 'خطا در پردازش: ' . $message);
    }

    public function resetState()
    {
        if ($this->filePath) {
            \Storage::delete($this->filePath);
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

    public function render()
    {
        return view('clients::user.settings.csv-importer');
    }
}
