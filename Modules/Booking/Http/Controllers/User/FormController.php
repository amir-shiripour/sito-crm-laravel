<?php

namespace Modules\Booking\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\BookingForm;
use Modules\Booking\Services\FormService;

class FormController extends Controller
{
    public function __construct(protected FormService $forms) {}

    public function index(Request $request)
    {
        $forms = $this->forms->paginate($request->user(), 20);

        return view('booking::user.forms.index', compact('forms'));
    }

    public function create()
    {
        $this->ensureFormCreateAllowed();
        $rolesQuery = \Spatie\Permission\Models\Role::orderBy('name');
        if (!auth()->user() || !auth()->user()->hasRole('super-admin')) {
            $rolesQuery->where('name', '!=', 'super-admin');
        }
        $roles = $rolesQuery->get();

        return view('booking::user.forms.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->ensureFormCreateAllowed();

        $data = $this->validateFormInput($request);

        $this->forms->create($request->user(), $data);

        return redirect()
            ->route('user.booking.forms.index')
            ->with('success', 'فرم با موفقیت ایجاد شد.');
    }

    public function edit(BookingForm $form)
    {
        $this->ensureFormEditAllowed();
        $this->ensureFormAccess($form);
        $rolesQuery = \Spatie\Permission\Models\Role::orderBy('name');
        if (!auth()->user() || !auth()->user()->hasRole('super-admin')) {
            $rolesQuery->where('name', '!=', 'super-admin');
        }
        $roles = $rolesQuery->get();

        return view('booking::user.forms.edit', compact('form', 'roles'));
    }

    public function update(Request $request, BookingForm $form)
    {
        $this->ensureFormEditAllowed();

        $data = $this->validateFormInput($request, $form);

        $this->forms->update($request->user(), $form, $data);

        return redirect()
            ->route('user.booking.forms.index')
            ->with('success', 'فرم با موفقیت بروزرسانی شد.');
    }

    public function destroy(Request $request, BookingForm $form)
    {
        $this->ensureFormDeleteAllowed();

        $this->forms->delete($request->user(), $form);

        return redirect()
            ->route('user.booking.forms.index')
            ->with('success', 'فرم با موفقیت حذف شد.');
    }

    protected function ensureFormCreateAllowed(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->can('booking.forms.create')) {
            abort(403);
        }
    }

    protected function ensureFormEditAllowed(): void
    {
        $user = auth()->user();
        if (! $user || ! ($user->can('booking.forms.edit') || $user->can('booking.forms.manage'))) {
            abort(403);
        }
    }

    protected function ensureFormDeleteAllowed(): void
    {
        $user = auth()->user();
        if (! $user || ! ($user->can('booking.forms.delete') || $user->can('booking.forms.manage'))) {
            abort(403);
        }
    }

    protected function ensureFormAccess(BookingForm $form): void
    {
        $user = auth()->user();
        $settings = \Modules\Booking\Entities\BookingSetting::current();

        if ($settings->form_management_scope === 'OWN' && $user && ! $user->can('booking.forms.manage') && ! $user->hasRole('super-admin')) {
            if ((int) $form->creator_id !== (int) $user->id) {
                abort(403);
            }
        }
    }

    protected function validateFormInput(Request $request, ?BookingForm $existingForm = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'form_type' => ['nullable', Rule::in([BookingForm::TYPE_CUSTOM, BookingForm::TYPE_TOOTH_NUMBER])],
            'status' => ['nullable', Rule::in([BookingForm::STATUS_ACTIVE, BookingForm::STATUS_INACTIVE])],
        ];

        if ($request->input('form_type') === BookingForm::TYPE_TOOTH_NUMBER) {
            $data = $request->validate($rules);
            $data['schema_json'] = [
                'fields' => [
                    [
                        'name' => 'tooth_numbers',
                        'label' => 'شماره دندان',
                        'type' => 'tooth_number',
                        'required' => false,
                        'collect_from_online' => false,
                        'placeholder' => null,
                        'options' => [],
                        'icon' => null,
                    ]
                ]
            ];
        } else {
            $rules['schema_json'] = ['required', 'array'];
            $rules['schema_json.fields'] = ['required', 'array', 'min:1'];
            $rules['schema_json.fields.*.name'] = ['nullable', 'string', 'max:100'];
            $rules['schema_json.fields.*.label'] = ['required', 'string', 'max:255'];
            $rules['schema_json.fields.*.type'] = ['required', 'string', 'max:50'];
            $rules['schema_json.fields.*.required'] = ['nullable'];
            $rules['schema_json.fields.*.collect_from_online'] = ['nullable'];
            $rules['schema_json.fields.*.placeholder'] = ['nullable', 'string', 'max:255'];
            $rules['schema_json.fields.*.options'] = ['nullable', 'string'];
            $rules['schema_json.fields.*.icon'] = ['nullable', 'string'];
            $rules['schema_json.fields.*.role'] = ['nullable', 'string', 'max:100'];
            $rules['schema_json.fields.*.multiple'] = ['nullable'];
            $rules['schema_json.fields.*.lock_current_if_role'] = ['nullable'];

            $data = $request->validate($rules);

            // استخراج تمام نام‌های فیلدهای سایر فرم‌های دیتابیس برای جلوگیری از نام‌های مشابه در دو فرم مختلف
            $otherFormsQuery = BookingForm::query();
            if ($existingForm && $existingForm->id) {
                $otherFormsQuery->where('id', '!=', $existingForm->id);
            }
            $existingGlobalNames = [];
            foreach ($otherFormsQuery->get(['schema_json']) as $otherForm) {
                foreach ($otherForm->schema_json['fields'] ?? [] as $f) {
                    if (!empty($f['name'])) {
                        $existingGlobalNames[strtolower(trim($f['name']))] = true;
                    }
                }
            }

            // نگهداری فیلدهای موجود قبلی خود این فرم برای جلوگیری از آسیب به نوبت‌های ثبت‌شده گذشته
            $originalCurrentNames = [];
            if ($existingForm && !empty($existingForm->schema_json['fields'])) {
                foreach ($existingForm->schema_json['fields'] as $f) {
                    if (!empty($f['name'])) {
                        $originalCurrentNames[strtolower(trim($f['name']))] = trim($f['name']);
                    }
                }
            }

            $fields = array_values($data['schema_json']['fields'] ?? []);
            $normalized = [];
            $usedInCurrentForm = [];

            foreach ($fields as $field) {
                $rawName = trim((string) ($field['name'] ?? ''));
                $label = trim((string) ($field['label'] ?? ''));
                $type = trim((string) ($field['type'] ?? 'text'));
                $placeholder = trim((string) ($field['placeholder'] ?? ''));
                $icon = trim((string) ($field['icon'] ?? ''));
                $role = trim((string) ($field['role'] ?? ''));
                $multiple = ! empty($field['multiple']);
                $lockCurrentIfRole = ! empty($field['lock_current_if_role']);

                // تعیین و یکتاسازی نام فیلد (جلوگیری از تشابه در همان فرم و سایر فرم‌ها)
                $name = $this->resolveUniqueFieldName(
                    $rawName,
                    $label,
                    $usedInCurrentForm,
                    $existingGlobalNames,
                    $originalCurrentNames
                );
                $usedInCurrentForm[strtolower($name)] = true;

                $optionsRaw = trim((string) ($field['options'] ?? ''));
                $options = $optionsRaw === ''
                    ? []
                    : array_values(array_filter(array_map('trim', explode(',', $optionsRaw)), fn($v) => $v !== ''));

                $normalized[] = [
                    'name' => $name,
                    'label' => $label,
                    'type' => $type ?: 'text',
                    'required' => ! empty($field['required']),
                    'collect_from_online' => ! empty($field['collect_from_online']),
                    'placeholder' => $placeholder ?: null,
                    'options' => $options,
                    'icon' => $icon ?: null,
                    'role' => $role ?: null,
                    'multiple' => $multiple,
                    'lock_current_if_role' => $lockCurrentIfRole,
                ];
            }

            $data['schema_json'] = [
                'fields' => $normalized,
            ];
        }

        // Ensure form_type is set, default to CUSTOM if not present
        $data['form_type'] = $data['form_type'] ?? BookingForm::TYPE_CUSTOM;

        return $data;
    }

    /**
     * تولید یا اعتبارسنجی نام یکتای فیلد (عدم تکرار در فرم فعلی و سایر فرم‌ها و سازگاری با گذشته)
     */
    protected function resolveUniqueFieldName(
        string $rawName,
        string $label,
        array $usedInCurrentForm,
        array $existingGlobalNames,
        array $originalCurrentNames
    ): string {
        $rawLower = strtolower($rawName);

        // اگر فیلد قبلاً در همین فرم با این نام وجود داشته و هنوز در فیلدهای این دور استفاده نشده، دقیقاً همان حفظ شود
        if ($rawName !== '' && isset($originalCurrentNames[$rawLower]) && !isset($usedInCurrentForm[$rawLower])) {
            return $rawName;
        }

        // تمیزسازی ورودی کاربر به صورت کلید انگلیسی استاندارد
        $sanitized = preg_replace('/[^a-zA-Z0-9_]/', '_', $rawName);
        $sanitized = trim((string) preg_replace('/_+/', '_', (string) $sanitized), '_');

        if ($sanitized === '') {
            $fromLabel = preg_replace('/[^a-zA-Z0-9_]/', '_', \Illuminate\Support\Str::slug($label, '_'));
            $fromLabel = trim((string) preg_replace('/_+/', '_', (string) $fromLabel), '_');
            $baseName = $fromLabel !== '' ? 'f_' . $fromLabel : 'field';
        } else {
            $baseName = $sanitized;
        }

        $candidate = $baseName;
        $candidateLower = strtolower($candidate);

        // اگر نام نامعتبر باشد یا در همین فرم یا سایر فرم‌های سیستم قبلاً استفاده شده باشد، شناسه یکتا اضافه می‌شود
        if ($candidate === '' || isset($usedInCurrentForm[$candidateLower]) || isset($existingGlobalNames[$candidateLower])) {
            do {
                $suffix = substr(bin2hex(random_bytes(3)), 0, 5);
                $candidate = ($baseName !== '' ? $baseName : 'field') . '_' . $suffix;
                $candidateLower = strtolower($candidate);
            } while (isset($usedInCurrentForm[$candidateLower]) || isset($existingGlobalNames[$candidateLower]));
        }

        return $candidate;
    }
}
