<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Morilog\Jalali\Jalalian;

class StoreProformaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->sanitizeNumericInputs();

        if ($this->filled('issue_date')) {
            $this->convertDateToGregorian('issue_date');
        }
        if ($this->filled('due_date')) {
            $this->convertDateToGregorian('due_date');
        }
    }

    public function rules(): array
    {
        $proformaId = $this->route('proforma') ? $this->route('proforma')->id : 'NULL';
        return [
            'client_id' => 'required|exists:clients,id',
            'proforma_number' => 'required|string|max:255|unique:proformas,proforma_number,' . $proformaId,
            'issue_date' => 'required|date_format:Y-m-d',
            'due_date' => 'nullable|date_format:Y-m-d|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'tax' => 'nullable|integer|min:0|max:100',
            'discount' => 'nullable|integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.item_code' => 'nullable|string|max:255',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_type' => 'required|string|max:50',
            'items.*.unit_price' => 'required|integer|min:0',
            'items.*.discount' => 'nullable|integer|min:0',
        ];
    }

    private function convertDateToGregorian(string $field): void
    {
        $date = $this->input($field);
        if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            try {
                $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                $englishDate = str_replace($persianDigits, $englishDigits, $date);
                $englishDate = str_replace('-', '/', $englishDate);
                $gregorianDate = Jalalian::fromFormat('Y/m/d', $englishDate)->toCarbon();
                $this->merge([$field => $gregorianDate->format('Y-m-d')]);
            } catch (\Exception $e) {
                $this->merge([$field => 'invalid_date']);
            }
        }
    }

    private function sanitizeNumericInputs(): void
    {
        $numberSanitizer = function ($value) {
            if (is_null($value) || $value === '') return null;
            if (!is_string($value) && !is_numeric($value)) return $value;

            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

            $strValue = str_replace($arabic, $english, str_replace($persian, $english, (string)$value));
            $noCommas = str_replace(',', '', $strValue);

            // Remove decimal parts since we only want integers
            if (strpos($noCommas, '.') !== false) {
                $noCommas = explode('.', $noCommas)[0];
            }

            // Return as integer
            return $noCommas === '' ? null : (int)$noCommas;
        };

        $this->merge([
            'tax' => $numberSanitizer($this->input('tax')),
            'discount' => $numberSanitizer($this->input('discount')),
        ]);

        if ($this->has('items')) {
            $sanitizedItems = collect($this->input('items'))->map(function ($item) use ($numberSanitizer) {
                $item['quantity'] = $numberSanitizer($item['quantity'] ?? null);
                $item['unit_price'] = $numberSanitizer($item['unit_price'] ?? null);
                $item['discount'] = $numberSanitizer($item['discount'] ?? null);
                return $item;
            })->all();
            $this->merge(['items' => $sanitizedItems]);
        }
    }
}
