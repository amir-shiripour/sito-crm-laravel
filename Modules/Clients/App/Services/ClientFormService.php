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
}
