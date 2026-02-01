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

        return view('booking::user.forms.create');
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

        return view('booking::user.forms.edit', compact('form'));
    }

    public function update(Request $request, BookingForm $form)
    {
        $this->ensureFormEditAllowed();

        $data = $this->validateFormInput($request);

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

    protected function validateFormInput(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'form_type' => ['nullable', Rule::in([BookingForm::TYPE_CUSTOM, BookingForm::TYPE_TOOTH_NUMBER])],
            'status' => ['nullable', Rule::in([BookingForm::STATUS_ACTIVE, BookingForm::STATUS_INACTIVE])],
            'schema_json' => ['required', 'array'],
            'schema_json.fields' => ['required', 'array', 'min:1'],
            'schema_json.fields.*.name' => ['required', 'string', 'max:100'],
            'schema_json.fields.*.label' => ['required', 'string', 'max:255'],
            'schema_json.fields.*.type' => ['required', 'string', 'max:50'],
            'schema_json.fields.*.required' => ['nullable'],
            'schema_json.fields.*.collect_from_online' => ['nullable'],
            'schema_json.fields.*.placeholder' => ['nullable', 'string', 'max:255'],
            'schema_json.fields.*.options' => ['nullable', 'string'],
            'schema_json.fields.*.icon' => ['nullable', 'string'],
        ]);

        $fields = array_values($data['schema_json']['fields'] ?? []);
        $normalized = [];

        foreach ($fields as $field) {
            $name = trim((string) ($field['name'] ?? ''));
            $label = trim((string) ($field['label'] ?? ''));
            $type = trim((string) ($field['type'] ?? 'text'));
            $placeholder = trim((string) ($field['placeholder'] ?? ''));
            $icon = trim((string) ($field['icon'] ?? ''));

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
            ];
        }

        $data['schema_json'] = [
            'fields' => $normalized,
        ];

        // Ensure form_type is set, default to CUSTOM if not present
        $data['form_type'] = $data['form_type'] ?? BookingForm::TYPE_CUSTOM;

        return $data;
    }
}
