@php
    /** @var \Modules\Booking\Entities\BookingForm $form */
    $fields = old('schema_json.fields', $form->schema_json['fields'] ?? []);
    if (!is_array($fields) || count($fields) === 0) {
        $fields = [
            ['label' => '', 'name' => '', 'type' => 'text', 'required' => false, 'placeholder' => '', 'options' => []],
        ];
    }
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="block text-sm mb-1">نام فرم</label>
        <input type="text" name="name" class="w-full border rounded p-2" value="{{ old('name', $form->name ?? '') }}" required>
        @error('name')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="block text-sm mb-1">وضعیت</label>
        <select name="status" class="w-full border rounded p-2">
            @php $status = old('status', $form->status ?? \Modules\Booking\Entities\BookingForm::STATUS_ACTIVE); @endphp
            <option value="ACTIVE" @selected($status === 'ACTIVE')>فعال</option>
            <option value="INACTIVE" @selected($status === 'INACTIVE')>غیرفعال</option>
        </select>
        @error('status')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
    </div>
</div>

<div class="border-t pt-4 space-y-3">
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold">فیلدهای فرم</h2>
        <button type="button" class="px-3 py-1 text-sm bg-indigo-600 text-white rounded" id="add-form-field">
            افزودن فیلد
        </button>
    </div>

    <div class="space-y-3" id="form-fields-container">
        @foreach($fields as $i => $field)
            @php
                $optionsValue = is_array($field['options'] ?? null)
                    ? implode(',', $field['options'])
                    : ($field['options'] ?? '');
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3 border rounded p-3 form-field-row">
                <div class="md:col-span-2">
                    <label class="block text-xs mb-1">برچسب</label>
                    <input type="text" name="schema_json[fields][{{ $i }}][label]" class="w-full border rounded p-2 text-sm"
                           value="{{ $field['label'] ?? '' }}" required>
                </div>

                <div>
                    <label class="block text-xs mb-1">نام فیلد</label>
                    <input type="text" name="schema_json[fields][{{ $i }}][name]" class="w-full border rounded p-2 text-sm"
                           value="{{ $field['name'] ?? '' }}" required>
                </div>

                <div>
                    <label class="block text-xs mb-1">نوع (HTML)</label>
                    @php $fieldType = $field['type'] ?? 'text'; @endphp
                    <select name="schema_json[fields][{{ $i }}][type]" class="w-full border rounded p-2 text-sm" required>
                        <option value="text" @selected($fieldType === 'text')>text</option>
                        <option value="number" @selected($fieldType === 'number')>number</option>
                        <option value="email" @selected($fieldType === 'email')>email</option>
                        <option value="tel" @selected($fieldType === 'tel')>tel</option>
                        <option value="date" @selected($fieldType === 'date')>date</option>
                        <option value="time" @selected($fieldType === 'time')>time</option>
                        <option value="textarea" @selected($fieldType === 'textarea')>textarea</option>
                        <option value="select" @selected($fieldType === 'select')>select</option>
                        <option value="radio" @selected($fieldType === 'radio')>radio</option>
                        <option value="checkbox" @selected($fieldType === 'checkbox')>checkbox</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs mb-1">Placeholder</label>
                    <input type="text" name="schema_json[fields][{{ $i }}][placeholder]" class="w-full border rounded p-2 text-sm"
                           value="{{ $field['placeholder'] ?? '' }}">
                </div>

                <div>
                    <label class="block text-xs mb-1">گزینه‌ها (CSV)</label>
                    <input type="text" name="schema_json[fields][{{ $i }}][options]" class="w-full border rounded p-2 text-sm"
                           value="{{ $optionsValue }}" placeholder="مثلاً: گزینه۱,گزینه۲">
                </div>

                <div class="flex items-end gap-3">
                    <label class="inline-flex items-center gap-2 text-xs">
                        <input type="checkbox" name="schema_json[fields][{{ $i }}][required]" value="1"
                            @checked(!empty($field['required']))>
                        ضروری
                    </label>
                    <button type="button" class="text-red-600 text-xs" onclick="this.closest('.form-field-row').remove()">
                        حذف
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    @error('schema_json.fields')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
    @error('schema_json.fields.*.name')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
    @error('schema_json.fields.*.label')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
    @error('schema_json.fields.*.type')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
</div>

<div class="flex items-center gap-3 pt-4">
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">ذخیره</button>
    <a class="text-gray-600 hover:underline" href="{{ route('user.booking.forms.index') }}">بازگشت</a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('form-fields-container');
        const addBtn = document.getElementById('add-form-field');

        if (!container || !addBtn) return;

        addBtn.addEventListener('click', function () {
            const index = Date.now();
            const row = document.createElement('div');
            row.className = 'grid grid-cols-1 md:grid-cols-6 gap-3 border rounded p-3 form-field-row';
            row.innerHTML = `
                <div class="md:col-span-2">
                    <label class="block text-xs mb-1">برچسب</label>
                    <input type="text" name="schema_json[fields][${index}][label]" class="w-full border rounded p-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-xs mb-1">نام فیلد</label>
                    <input type="text" name="schema_json[fields][${index}][name]" class="w-full border rounded p-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-xs mb-1">نوع (HTML)</label>
                    <select name="schema_json[fields][${index}][type]" class="w-full border rounded p-2 text-sm" required>
                        <option value="text">text</option>
                        <option value="number">number</option>
                        <option value="email">email</option>
                        <option value="tel">tel</option>
                        <option value="date">date</option>
                        <option value="time">time</option>
                        <option value="textarea">textarea</option>
                        <option value="select">select</option>
                        <option value="radio">radio</option>
                        <option value="checkbox">checkbox</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs mb-1">Placeholder</label>
                    <input type="text" name="schema_json[fields][${index}][placeholder]" class="w-full border rounded p-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs mb-1">گزینه‌ها (CSV)</label>
                    <input type="text" name="schema_json[fields][${index}][options]" class="w-full border rounded p-2 text-sm" placeholder="مثلاً: گزینه۱,گزینه۲">
                </div>
                <div class="flex items-end gap-3">
                    <label class="inline-flex items-center gap-2 text-xs">
                        <input type="checkbox" name="schema_json[fields][${index}][required]" value="1">
                        ضروری
                    </label>
                    <button type="button" class="text-red-600 text-xs" onclick="this.closest('.form-field-row').remove()">حذف</button>
                </div>
            `;
            container.appendChild(row);
        });
    });
</script>
