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
    public $filePath;
    public $hasHeaders = true;

    public $csvHeaders = [];
    public $formFields = [];
    public $fieldMapping = [];

    public $importing = false;
    public $importCount = 0;
    public $updateCount = 0;
    public $importErrors = [];

    public $updateExisting = false;

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
        $fields = [];

        foreach (ClientForm::systemFieldDefaults() as $id => $field) {
            $fields[$id] = $field['label'] . " (سیستمی)";
        }

        if ($activeForm) {
            foreach ($activeForm->schema['fields'] ?? [] as $field) {
                if (!isset($fields[$field['id']])) {
                    $fields[$field['id']] = $field['label'];
                }
            }
        }
        $this->formFields = $fields;
    }

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
                $file->rewind();
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
            'نام' => 'full_name', 'نام و نام خانوادگی' => 'full_name',
            'موبایل' => 'phone', 'تلفن' => 'phone',
            'ایمیل' => 'email', 'کدملی' => 'national_code',
            'کد ملی' => 'national_code', 'شماره پرونده' => 'case_number',
            'یادداشت' => 'notes', 'وضعیت' => 'status_id',
            'رمز عبور' => 'password',
        ];

        if (isset($commonMappings[$header])) return $commonMappings[$header];
        if (array_key_exists($header, $this->formFields)) return $header;
        return '';
    }

    public function processImport()
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        ini_set('auto_detect_line_endings', TRUE);

        $this->validate(['fieldMapping' => 'required|array']);

        $this->importing = true;
        $this->importCount = 0;
        $this->updateCount = 0;
        $this->importErrors = [];

        try {
            if (!Storage::exists($this->filePath)) {
                throw new \Exception('فایل منقضی شده یا حذف شده است. لطفاً دوباره آپلود کنید.');
            }

            $path = Storage::path($this->filePath);
            $file = new SplFileObject($path, 'r');
            $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

            $defaultStatus = ClientStatus::where('is_active', true)->orderBy('sort_order')->first() ?? ClientStatus::first();
            $usernameStrategy = ClientSetting::getValue('username_strategy', 'email_local');

            $file->rewind();
            if ($this->hasHeaders) {
                $file->fgetcsv();
            }

            while (!$file->eof()) {
                $row = $file->fgetcsv();
                $rowNumber = $file->key();

                if ($row === false || empty(array_filter($row, fn($value) => $value !== null && $value !== ''))) {
                    continue;
                }

                $clientData = [];
                $metaData = [];

                foreach ($this->fieldMapping as $index => $fieldId) {
                    if (empty($fieldId) || !isset($row[$index])) continue;
                    $value = trim($row[$index]);
                    if ($value === '') continue;

                    if (ClientForm::isSystemFieldId($fieldId)) {
                        $clientData[$fieldId] = $value;
                    } else {
                        $metaData[$fieldId] = $value;
                    }
                }

                if (empty($clientData) && empty($metaData)) continue;

                if ($this->updateExisting && empty($clientData['phone']) && empty($clientData['email']) && empty($clientData['national_code']) && empty($clientData['case_number'])) {
                    $this->importErrors[] = ['row' => $rowNumber, 'error' => 'برای آپدیت، حداقل یکی از فیلدهای "موبایل"، "ایمیل"، "کد ملی" یا "شماره پرونده" الزامی است.'];
                    continue;
                }

                if (!empty($clientData['phone'])) {
                    $phone = preg_replace('/[^0-9]/', '', $clientData['phone']);
                    if (!str_starts_with($phone, '0') && strlen($phone) === 10) {
                        $phone = '0' . $phone;
                    }
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
                    if (empty($clientData['username'])) unset($clientData['username']);
                    if (empty($clientData['password'])) {
                        unset($clientData['password']);
                    } else {
                        $clientData['password'] = Hash::make($clientData['password'], ['rounds' => 4]);
                    }
                    if (!empty($metaData)) {
                        $clientData['meta'] = array_merge($existingClient->meta ?? [], $metaData);
                    }
                    if (!empty($clientData['status_id'])) {
                        $status = ClientStatus::where('label', $clientData['status_id'])->orWhere('key', $clientData['status_id'])->first();
                        $clientData['status_id'] = $status->id ?? $existingClient->status_id;
                    }

                    $existingClient->update($clientData);
                    $this->updateCount++;
                } else {
                    if (empty($clientData['full_name'])) {
                        $this->importErrors[] = ['row' => $rowNumber, 'error' => 'فیلد "نام و نام خانوادگی" برای ایجاد کاربر جدید الزامی است.'];
                        continue;
                    }
                    if (empty($clientData['username'])) {
                        $clientData['username'] = $this->generateUsername($clientData);
                    }
                    if ($usernameStrategy === 'mobile' && !empty($clientData['username']) && Client::where('username', $clientData['username'])->exists()) {
                        $this->importErrors[] = ['row' => $rowNumber, 'error' => "کاربر با شماره موبایل (نام کاربری) {$clientData['username']} قبلاً ثبت شده است."];
                        continue;
                    }
                    if (isset($clientData['password'])) {
                        $clientData['password'] = Hash::make($clientData['password'], ['rounds' => 4]);
                    } else {
                        $clientData['password'] = Hash::make(Str::random(12), ['rounds' => 4]);
                    }
                    if (!empty($clientData['status_id'])) {
                        $status = ClientStatus::where('label', $clientData['status_id'])->orWhere('key', $clientData['status_id'])->first();
                        $clientData['status_id'] = $status->id ?? $defaultStatus?->id;
                    } else {
                         $clientData['status_id'] = $defaultStatus?->id;
                    }
                    $clientData['created_by'] = auth()->id();
                    $clientData['meta'] = $metaData;
                    Client::create($clientData);
                    $this->importCount++;
                }
            }

            $this->dispatch('notify', type: 'success', text: "عملیات با موفقیت انجام شد. {$this->importCount} مشتری جدید ایجاد و {$this->updateCount} مشتری آپدیت شد.");
            if (count($this->importErrors) > 0) {
                $this->dispatch('notify', type: 'warning', text: count($this->importErrors) . " رکورد با خطا مواجه شد.");
            }

        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', text: 'خطا در پردازش فایل: ' . $e->getMessage());
        } finally {
            $this->importing = false;
        }
    }

    protected function generateUsername(array $data): string
    {
        $strategy = ClientSetting::getValue('username_strategy', 'email_local');
        $prefix = ClientSetting::getValue('username_prefix', 'clt');
        $base = '';

        switch ($strategy) {
            case 'mobile': $base = $data['phone'] ?? ''; break;
            case 'national_code': $base = $data['national_code'] ?? ''; break;
            case 'email_local': $base = explode('@', $data['email'] ?? '')[0]; break;
            case 'name_increment':
                $name = Str::slug($data['full_name'] ?? 'user', '_');
                $base = $name . '_' . rand(100, 999);
                break;
            case 'prefix_increment':
                $base = $prefix . '-' . rand(10000, 99999);
                break;
            default: $base = 'user_' . Str::random(6);
        }

        $base = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $base);
        if (empty($base)) $base = 'user_' . Str::random(8);

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
        $this->updateCount = 0;
        $this->importErrors = [];
        $this->redirect(route('user.settings.clients.import'));
    }

    public function render()
    {
        return view('clients::user.settings.csv-importer');
    }
}
