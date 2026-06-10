<?php

namespace Modules\Clients\App\Livewire\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Modules\Clients\Entities\ClientForm;
use Modules\Clients\Entities\Client;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.user')]
class CsvExporter extends Component
{
    public $formFields = [];
    public $selectedFields = [];
    public $hasHeaders = true;

    public function mount()
    {
        $this->loadFormFields();
        // انتخاب همه فیلدها به صورت پیش‌فرض
        $this->selectedFields = array_keys($this->formFields);
    }

    protected function loadFormFields()
    {
        $activeForm = ClientForm::active();
        $fields = [];
        
        // فیلدهای سیستمی
        foreach (ClientForm::systemFieldDefaults() as $id => $field) {
            $fields[$id] = $field['label'] . " (سیستمی)";
        }
        
        // فیلدهای فرم‌ساز
        if ($activeForm) {
            foreach ($activeForm->schema['fields'] ?? [] as $field) {
                if (!isset($fields[$field['id']])) {
                    $fields[$field['id']] = $field['label'];
                }
            }
        }
        
        $this->formFields = $fields;
    }

    public function addField($fieldId)
    {
        if (!in_array($fieldId, $this->selectedFields) && isset($this->formFields[$fieldId])) {
            $this->selectedFields[] = $fieldId;
        }
    }

    public function removeField($fieldId)
    {
        $this->selectedFields = array_values(array_filter($this->selectedFields, fn($f) => $f !== $fieldId));
    }

    public function moveUp($index)
    {
        if ($index > 0 && isset($this->selectedFields[$index])) {
            $temp = $this->selectedFields[$index];
            $this->selectedFields[$index] = $this->selectedFields[$index - 1];
            $this->selectedFields[$index - 1] = $temp;
            $this->selectedFields = array_values($this->selectedFields);
        }
    }

    public function moveDown($index)
    {
        if ($index < count($this->selectedFields) - 1 && isset($this->selectedFields[$index])) {
            $temp = $this->selectedFields[$index];
            $this->selectedFields[$index] = $this->selectedFields[$index + 1];
            $this->selectedFields[$index + 1] = $temp;
            $this->selectedFields = array_values($this->selectedFields);
        }
    }

    public function addAll()
    {
        $this->selectedFields = array_keys($this->formFields);
    }

    public function removeAll()
    {
        $this->selectedFields = [];
    }

    public function export()
    {
        $this->validate([
            'selectedFields' => 'required|array|min:1',
        ], [
            'selectedFields.required' => 'حداقل یک ستون را برای خروجی انتخاب کنید.',
            'selectedFields.min' => 'حداقل یک ستون را برای خروجی انتخاب کنید.',
        ]);

        $exportFields = [];
        foreach ($this->selectedFields as $fieldId) {
            if (isset($this->formFields[$fieldId])) {
                $exportFields[$fieldId] = str_replace(' (سیستمی)', '', $this->formFields[$fieldId]);
            }
        }

        $fileName = 'clients_export_' . now()->format('Y_m_d_His') . '.csv';

        return response()->streamDownload(function () use ($exportFields) {
            $handle = fopen('php://output', 'w');
            
            // افزودن BOM برای پشتیبانی از حروف فارسی در اکسل
            fwrite($handle, "\xEF\xBB\xBF");
            
            // نوشتن هدرها در صورت فعال بودن گزینه
            if ($this->hasHeaders) {
                fputcsv($handle, array_values($exportFields));
            }
            
            // دریافت کلاینت‌ها با اعمال دسترسی‌ها
            $query = Client::visibleForUser(auth()->user())->with('status');
            
            // پردازش کلاینت‌ها به صورت بخش به بخش برای جلوگیری از پر شدن رم
            $query->chunk(200, function ($clients) use ($handle, $exportFields) {
                foreach ($clients as $client) {
                    $row = [];
                    foreach (array_keys($exportFields) as $fieldId) {
                        if (ClientForm::isSystemFieldId($fieldId)) {
                            if ($fieldId === 'status_id') {
                                $row[] = $client->status ? $client->status->label : '';
                            } elseif ($fieldId === 'password') {
                                $row[] = '***'; // رمز عبور نباید خروجی داده شود
                            } else {
                                $row[] = $client->{$fieldId} ?? '';
                            }
                        } else {
                            // خواندن از متادیتا برای فیلدهای اختصاصی
                            $row[] = $client->meta[$fieldId] ?? '';
                        }
                    }
                    fputcsv($handle, $row);
                }
            });
            
            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render()
    {
        return view('clients::user.settings.csv-exporter');
    }
}
