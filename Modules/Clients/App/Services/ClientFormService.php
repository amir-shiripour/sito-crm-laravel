<?php

namespace Modules\Clients\App\Services;

use Illuminate\Validation\Rule;
use Modules\Clients\Entities\ClientForm;
use Modules\Clients\Entities\ClientSetting;

class ClientFormService
{
    protected ?ClientForm $activeForm = null;

    public function __construct()
    {
        $this->activeForm = $this->getActiveForm();
    }

    /**
     * Get the currently active client form.
     */
    public function getActiveForm(): ?ClientForm
    {
        $preferredKey = ClientSetting::getValue('default_form_key');
        return ClientForm::active($preferredKey);
    }

    /**
     * Get the fields from the active form's schema.
     */
    public function getFormFields(): array
    {
        if (!$this->activeForm) {
            return [];
        }

        return $this->activeForm->schema['fields'] ?? [];
    }

    /**
     * Generate validation rules based on the form schema.
     *
     * @param bool $isUpdate Whether this is for an update operation.
     * @param int|null $clientId The ID of the client being updated.
     * @return array
     */
    public function getValidationRules(bool $isUpdate = false, ?int $clientId = null): array
    {
        $rules = [];
        $fields = $this->getFormFields();

        foreach ($fields as $field) {
            $fieldId = $field['id'];
            $fieldRules = [];

            // Handle required fields
            if (isset($field['required']) && $field['required']) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Handle field types
            switch ($field['type']) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'file':
                    $fieldRules[] = 'file';
                    if (isset($field['max_mb'])) {
                        $fieldRules[] = 'max:' . ($field['max_mb'] * 1024);
                    }
                    if (isset($field['accept'])) {
                        $fieldRules[] = 'mimes:' . str_replace('.', '', $field['accept']);
                    }
                    break;
            }

            // Handle system fields with unique constraints
            if (ClientForm::isSystemFieldId($fieldId)) {
                if (in_array($fieldId, ['email', 'phone', 'username', 'national_code'])) {
                    $uniqueRule = Rule::unique('clients', $fieldId);
                    if ($isUpdate && $clientId) {
                        $uniqueRule->ignore($clientId);
                    }
                    $fieldRules[] = $uniqueRule;
                }
            }

            // Add string and max length for text-based inputs
            if (in_array($field['type'], ['text', 'textarea', 'email', 'password'])) {
                $fieldRules[] = 'string';
                if ($field['type'] !== 'textarea') {
                    $fieldRules[] = 'max:255';
                }
            }

            if ($fieldId === 'password' && !$isUpdate) {
                $fieldRules[] = 'confirmed';
                $fieldRules[] = 'min:8';
            }

            $rules[$fieldId] = $fieldRules;
        }

        return $rules;
    }

    /**
     * Save dynamically created options globally to the form schema if enabled.
     *
     * @param array $payload
     * @return void
     */
    public function saveNewOptionsFromPayload(array $payload): void
    {
        $form = $this->activeForm;
        if (!$form) {
            return;
        }

        $fields = $form->schema['fields'] ?? [];
        $schemaUpdated = false;

        foreach ($fields as $index => $field) {
            if (($field['type'] ?? null) !== 'select') {
                continue;
            }

            if (empty($field['creatable']) || empty($field['save_globally'])) {
                continue;
            }

            $fieldId = $field['id'];
            $value = $payload[$fieldId] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            // Handle both single and multiple values
            $valuesToCheck = is_array($value) ? $value : [$value];
            if (is_string($value) && str_starts_with($value, '[') && str_ends_with($value, ']')) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $valuesToCheck = $decoded;
                }
            }

            // Parse existing options
            $existingOptions = [];
            $optionsJson = $field['options_json'] ?? '';
            if (is_string($optionsJson) && trim($optionsJson) !== '') {
                // Check if it's JSON or lines
                $decoded = json_decode($optionsJson, true);
                if (is_array($decoded)) {
                    $existingOptions = $decoded;
                } else {
                    $lines = array_filter(array_map('trim', explode("\n", $optionsJson)));
                    foreach ($lines as $line) {
                        if (str_contains($line, ':')) {
                            [$k, $v] = array_map('trim', explode(':', $line, 2));
                            $existingOptions[$k] = $v;
                        } else {
                            $existingOptions[$line] = $line;
                        }
                    }
                }
            }

            $optionsChanged = false;
            foreach ($valuesToCheck as $val) {
                $valStr = (string)$val;
                if (!isset($existingOptions[$valStr]) && !in_array($valStr, $existingOptions, true)) {
                    // Option is new, append it
                    $existingOptions[$valStr] = $valStr;
                    $optionsChanged = true;
                }
            }

            if ($optionsChanged) {
                // Re-serialize options back to the original format (lines or JSON)
                // Let's keep it as lines if it was lines, or JSON. If it's empty, default to lines.
                // Let's check if the original was JSON.
                $wasJson = is_string($optionsJson) && str_starts_with(trim($optionsJson), '{');
                if ($wasJson) {
                    $fields[$index]['options_json'] = json_encode($existingOptions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                } else {
                    $lines = [];
                    foreach ($existingOptions as $k => $v) {
                        if ($k === $v) {
                            $lines[] = $k;
                        } else {
                            $lines[] = "$k:$v";
                        }
                    }
                    $fields[$index]['options_json'] = implode("\n", $lines);
                }
                $schemaUpdated = true;
            }
        }

        if ($schemaUpdated) {
            $schema = $form->schema;
            $schema['fields'] = $fields;
            $form->schema = $schema;
            $form->save();
        }
    }
}
