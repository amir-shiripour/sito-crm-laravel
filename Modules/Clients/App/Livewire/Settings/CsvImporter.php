<?php

namespace Modules\Clients\App\Livewire\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Modules\Clients\Entities\ClientForm;
use Modules\Clients\Entities\ClientStatus;
use Modules\Clients\Entities\ClientSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Clients\Entities\Client;
use Illuminate\Support\Facades\Storage;
use SplFileObject;

#[Layout('layouts.user')]
class CsvImporter extends Component
{
    // use WithFileUploads; // Removed

    public $filePath; // Path to the uploaded file in storage
    public $hasHeaders = true;

    public $csvHeaders = [];
    public $formFields = [];
    public $fieldMapping = [];

    public $importing = false;
    public $importCount = 0;
    public $importErrors = [];

    protected $queryString = ['filePath' => ['as' => 'file']];

    public function mount()
    {
        $this->loadFormFields();

        if ($this->filePath) {
            $this->parseCsvHeaders();
        }
    }

    protected function loadFormFields()
    {
        $activeForm = ClientForm::active();
        if (!$activeForm) {
            $this->formFields = [];
            return;
        }

        $fields = [];
        // Add system fields
        foreach (ClientForm::systemFieldDefaults() as $id => $field) {
            $fields[$id] = $field['label'] . " (سیستمی)";
        }

        // Add custom fields from schema
        foreach ($activeForm->schema['fields'] ?? [] as $field) {
            if (!isset($fields[$field['id']])) { // Avoid overwriting system fields
                $fields[$field['id']] = $field['label'];
            }
        }

        $this->formFields = $fields;
    }

    // Called when hasHeaders checkbox is toggled
    public function updatedHasHeaders()
    {
        if ($this->filePath) {
            $this->parseCsvHeaders();
        }
    }

    protected function parseCsvHeaders()
    {
        try {
            if (!Storage::exists($this->filePath)) {
                $this->dispatch('notify', type: 'error', text: 'فایل یافت نشد یا حذف شده است.');
                $this->filePath = null;
                return;
            }

            $path = Storage::path($this->filePath);
            $file = new SplFileObject($path, 'r');
            $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

            if ($this->hasHeaders) {
                $headers = $file->fgetcsv();
                if(!$headers) {
                    $this->dispatch('notify', type: 'error', text: 'فایل CSV خالی یا نامعتبر است.');
                    return;
                }
                $this->csvHeaders = $headers;

                // Auto-map fields
                foreach ($headers as $index => $header) {
                    $this->fieldMapping[$index] = $this->guessField($header);
                }
            } else {
                $firstRow = $file->fgetcsv();
                 if(!$firstRow) {
                    $this->dispatch('notify', type: 'error', text: 'فایل CSV خالی یا نامعتبر است.');
                    return;
                }
                $this->csvHeaders = array_map(fn($i) => "ستون " . ($i + 1), array_keys($firstRow));
                $this->fieldMapping = array_fill(0, count($this->csvHeaders), '');
                $file->rewind(); // Go back to the beginning
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', text: 'فایل CSV نامعتبر است: ' . $e->getMessage());
            $this->csvHeaders = [];
            $this->fieldMapping = [];
        }
    }

    protected function guessField($header)
    {
        $header = trim(mb_strtolower($header));
        $commonMappings = [
            'نام' => 'full_name',
            'نام و نام خانوادگی' => 'full_name',
            'موبایل' => 'phone',
            'تلفن' => 'phone',
            'ایمیل' => 'email',
            'کدملی' => 'national_code',
            'کد ملی' => 'national_code',
            'یادداشت' => 'notes',
            'وضعیت' => 'status_id',
            'رمز عبور' => 'password',
        ];

        if (isset($commonMappings[$header])) {
            return $commonMappings[$header];
        }

        // Direct match with field IDs
        if (array_key_exists($header, $this->formFields)) {
            return $header;
        }

        return ''; // No guess
    }

    public function processImport()
    {
        // Increase execution time
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $this->validate(['fieldMapping' => 'required|array']);

        $this->importing = true;
        $this->importCount = 0;
        $this->importErrors = [];

        try {
            if (!Storage::exists($this->filePath)) {
                throw new \Exception('فایل منقضی شده یا حذف شده است. لطفاً دوباره آپلود کنید.');
            }

            $path = Storage::path($this->filePath);
            $file = new SplFileObject($path, 'r');
            $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

            if ($this->hasHeaders) {
                $file->fgetcsv(); // Skip header row
            }

            // Find default status (first active status by sort order)
            $defaultStatus = ClientStatus::where('is_active', true)->orderBy('sort_order')->first();

            // Get username strategy
            $usernameStrategy = ClientSetting::getValue('username_strategy')
                ?? ClientSetting::getValue('username.strategy', 'email_local');

            foreach ($file as $rowNumber => $row) {
                // Check if row is valid array
                if (!is_array($row) || empty(array_filter($row))) continue; // Skip empty rows

                $clientData = [];
                $customData = [];

                foreach ($this->fieldMapping as $index => $fieldId) {
                    if (empty($fieldId) || !isset($row[$index])) continue;
                    $value = trim($row[$index]);
                    if ($value === '') continue;

                    if (ClientForm::isSystemFieldId($fieldId)) {
                        $clientData[$fieldId] = $value;
                    } else {
                        $customData[$fieldId] = $value;
                    }
                }

                if (empty($clientData) && empty($customData)) continue;

                if (empty($clientData['full_name'])) {
                    $this->importErrors[] = ['row' => $rowNumber + 2, 'error' => 'فیلد "نام و نام خانوادگی" الزامی است.'];
                    continue;
                }

                // Normalize Phone Number (Add leading zero if missing)
                if (!empty($clientData['phone'])) {
                    // Remove any non-digit characters first to be safe
                    $phone = preg_replace('/[^0-9]/', '', $clientData['phone']);

                    // اگر شماره با 0 شروع نمی‌شود و طول آن 10 رقم است (فرمت موبایل ایران بدون صفر)
                    if (!str_starts_with($phone, '0') && strlen($phone) === 10) {
                        $phone = '0' . $phone;
                    }
                    $clientData['phone'] = $phone;
                }

                if (!empty($clientData['status_id'])) {
                    $status = ClientStatus::where('label', $clientData['status_id'])->orWhere('key', $clientData['status_id'])->first();
                    $clientData['status_id'] = $status->id ?? $defaultStatus?->id;
                } else {
                    $clientData['status_id'] = $defaultStatus?->id;
                }

                // Fallback if no status found
                if (empty($clientData['status_id'])) {
                     // If still no status, try to find ANY status
                     $anyStatus = ClientStatus::first();
                     $clientData['status_id'] = $anyStatus?->id;
                }

                // Generate Username if not provided
                if (empty($clientData['username'])) {
                    $clientData['username'] = $this->generateUsername($clientData);
                }

                // Check for duplicate username if strategy is mobile
                if ($usernameStrategy === 'mobile') {
                    if (Client::where('username', $clientData['username'])->exists()) {
                        $this->importErrors[] = ['row' => $rowNumber + 2, 'error' => "کاربر با شماره موبایل (نام کاربری) {$clientData['username']} قبلاً ثبت شده است."];
                        continue;
                    }
                }

                // Use lower cost for hashing to speed up import
                $password = isset($clientData['password']) ? $clientData['password'] : Str::random(12);
                $clientData['password'] = Hash::make($password, ['rounds' => 4]); // Lower rounds for speed

                $clientData['created_by'] = auth()->id();
                $clientData['custom_fields'] = $customData;

                Client::create($clientData);
                $this->importCount++;
            }

            $this->dispatch('notify', type: 'success', text: "{$this->importCount} مشتری با موفقیت ایمپورت شد.");
            if (count($this->importErrors) > 0) {
                $this->dispatch('notify', type: 'warning', text: count($this->importErrors) . " رکورد با خطا مواجه شد.");
            }

            // Optional: Delete file after successful import
            // Storage::delete($this->filePath);
            // $this->resetImport();

        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', text: 'خطا در پردازش فایل: ' . $e->getMessage());
        } finally {
            $this->importing = false;
        }
    }

    protected function generateUsername(array $data): string
    {
        $strategy = ClientSetting::getValue('username_strategy')
            ?? ClientSetting::getValue('username.strategy', 'email_local');

        $prefix = ClientSetting::getValue('username_prefix')
            ?? ClientSetting::getValue('username.prefix', 'clt');

        $base = '';

        switch ($strategy) {
            case 'mobile':
                $base = $data['phone'] ?? '';
                break;
            case 'national_code':
                $base = $data['national_code'] ?? '';
                break;
            case 'email_local':
                $email = $data['email'] ?? '';
                $base = explode('@', $email)[0];
                break;
            case 'name_increment': // name_rand in UI
                // Simple implementation for now: name + random number
                // A better implementation would check for uniqueness and increment
                $name = Str::slug($data['full_name'] ?? 'user', '_');
                $base = $name . '_' . rand(100, 999);
                break;
            case 'prefix_increment':
                // This is tricky in bulk import because we need unique sequential numbers.
                // For simplicity, we'll use prefix + timestamp + random for now to avoid collision in loop
                // Or we can query DB for max ID, but that's slow in loop.
                // Let's use a random approach for bulk import safety or just prefix + random
                $base = $prefix . '-' . rand(10000, 99999);
                break;
            default:
                $base = 'user_' . Str::random(6);
        }

        // Clean up base
        $base = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $base);

        if (empty($base)) {
             $base = 'user_' . Str::random(8);
        }

        // Ensure uniqueness ONLY if strategy is NOT mobile (for mobile we want to skip duplicates)
        if ($strategy !== 'mobile') {
            $username = $base;
            $counter = 1;
            while (Client::where('username', $username)->exists()) {
                $username = $base . '_' . $counter++;
            }
            return $username;
        }

        return $base;
    }

    public function resetImport()
    {
        if ($this->filePath) {
            Storage::delete($this->filePath);
        }
        $this->filePath = null;
        $this->csvHeaders = [];
        $this->fieldMapping = [];
        $this->importing = false;
        $this->importCount = 0;
        $this->importErrors = [];

        // Clear query string
        $this->redirect(route('user.settings.clients.import'));
    }

    public function render()
    {
        return view('clients::user.settings.csv-importer');
    }
}
