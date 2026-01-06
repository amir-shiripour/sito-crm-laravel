@php
    /** @var \Modules\Booking\Entities\BookingForm $form */
    $fields = old('schema_json.fields', $form->schema_json['fields'] ?? []);
    if (!is_array($fields) || count($fields) === 0) {
    $fields = [
    ['label' => '', 'name' => '', 'type' => 'text', 'required' => false, 'placeholder' => '', 'options' => []],
    ];
    }

    $inputClass = 'w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/60 px-3 py-2
    text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:border-indigo-400
    dark:focus:ring-indigo-500/30 transition';
    $selectClass = $inputClass;
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';
    $smallLabelClass = 'block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1';
    $errorClass = 'text-xs text-rose-600 dark:text-rose-400 mt-1';
    $fieldCardClasses = 'grid grid-cols-1 md:grid-cols-6 gap-3 rounded-xl border border-gray-200 dark:border-gray-700
    bg-gray-50/60 dark:bg-gray-900/40 p-3 form-field-row';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="{{ $labelClass }}">نام فرم</label>
        <input type="text" name="name" class="{{ $inputClass }}" value="{{ old('name', $form->name ?? '') }}" required>
        @error('name')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="{{ $labelClass }}">وضعیت</label>
        <select name="status" class="{{ $selectClass }}">
            @php $status = old('status', $form->status ?? \Modules\Booking\Entities\BookingForm::STATUS_ACTIVE); @endphp
            <option value="ACTIVE" @selected($status==='ACTIVE' )>فعال</option>
            <option value="INACTIVE" @selected($status==='INACTIVE' )>غیرفعال</option>
        </select>
        @error('status')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
    </div>
</div>

<div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-3">
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">فیلدهای فرم</h2>
        <button type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition"
                id="add-form-field">
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
            <div class="{{ $fieldCardClasses }}">
                <div class="md:col-span-2">
                    <label class="{{ $smallLabelClass }}">برچسب</label>
                    <input type="text" name="schema_json[fields][{{ $i }}][label]" class="{{ $inputClass }} text-sm"
                           value="{{ $field['label'] ?? '' }}" required>
                </div>

                <div>
                    <label class="{{ $smallLabelClass }}">نام فیلد</label>
                    <input type="text" name="schema_json[fields][{{ $i }}][name]" class="{{ $inputClass }} text-sm"
                           value="{{ $field['name'] ?? '' }}" required>
                </div>

                <div>
                    <label class="{{ $smallLabelClass }}">نوع (HTML)</label>
                    @php $fieldType = $field['type'] ?? 'text'; @endphp
                    <select name="schema_json[fields][{{ $i }}][type]" class="{{ $selectClass }} text-sm" required>
                        <option value="text" @selected($fieldType==='text' )>text</option>
                        <option value="number" @selected($fieldType==='number' )>number</option>
                        <option value="email" @selected($fieldType==='email' )>email</option>
                        <option value="tel" @selected($fieldType==='tel' )>tel</option>
                        <option value="date" @selected($fieldType==='date' )>date</option>
                        <option value="time" @selected($fieldType==='time' )>time</option>
                        <option value="textarea" @selected($fieldType==='textarea' )>textarea</option>
                        <option value="select" @selected($fieldType==='select' )>select</option>
                        <option value="radio" @selected($fieldType==='radio' )>radio</option>
                        <option value="checkbox" @selected($fieldType==='checkbox' )>checkbox</option>
                    </select>
                </div>

                <div>
                    <label class="{{ $smallLabelClass }}">Placeholder</label>
                    <input type="text" name="schema_json[fields][{{ $i }}][placeholder]" class="{{ $inputClass }} text-sm"
                           value="{{ $field['placeholder'] ?? '' }}">
                </div>

                <div>
                    <label class="{{ $smallLabelClass }}">گزینه‌ها (CSV)</label>
                    <input type="text" name="schema_json[fields][{{ $i }}][options]" class="{{ $inputClass }} text-sm"
                           value="{{ $optionsValue }}" placeholder="مثلاً: گزینه۱,گزینه۲">
                </div>

                <div class="flex items-end gap-3">
                    <label class="inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="schema_json[fields][{{ $i }}][required]" value="1"
                            @checked(!empty($field['required']))>
                        ضروری
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="schema_json[fields][{{ $i }}][collect_from_online]" value="1"
                            @checked(!empty($field['collect_from_online']))>
                        دریافت از کاربر آنلاین
                    </label>
                    <button type="button" class="text-rose-600 dark:text-rose-300 text-xs font-medium hover:underline"
                            onclick="this.closest('.form-field-row').remove()">
                        حذف
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    @error('schema_json.fields')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
    @error('schema_json.fields.*.name')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
    @error('schema_json.fields.*.label')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
    @error('schema_json.fields.*.type')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
</div>

<div class="flex items-center gap-3 pt-4">
    <button type="submit"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition">ذخیره</button>
    <a class="inline-flex items-center gap-1 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition"
       href="{{ route('user.booking.forms.index') }}">بازگشت</a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('form-fields-container');
        const addBtn = document.getElementById('add-form-field');
        const inputClass = @json($inputClass.
        ' text-sm');
        const selectClass = @json($selectClass.
        ' text-sm');
        const smallLabelClass = @json($smallLabelClass);
        const fieldCardClass = @json($fieldCardClasses);

        if (!container || !addBtn) return;

        addBtn.addEventListener('click', function() {
            const index = Date.now();
            const row = document.createElement('div');
            row.className = fieldCardClass;
            row.innerHTML = `
                <div class="md:col-span-2">
                    <label class="${smallLabelClass}">برچسب</label>
                    <input type="text" name="schema_json[fields][${index}][label]" class="${inputClass}" required>
                </div>
                <div>
                    <label class="${smallLabelClass}">نام فیلد</label>
                    <input type="text" name="schema_json[fields][${index}][name]" class="${inputClass}" required>
                </div>
                <div>
                    <label class="${smallLabelClass}">نوع (HTML)</label>
                    <select name="schema_json[fields][${index}][type]" class="${selectClass}" required>
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
                    <label class="${smallLabelClass}">Placeholder</label>
                    <input type="text" name="schema_json[fields][${index}][placeholder]" class="${inputClass}">
                </div>
                <div>
                    <label class="${smallLabelClass}">گزینه‌ها (CSV)</label>
                    <input type="text" name="schema_json[fields][${index}][options]" class="${inputClass}" placeholder="مثلاً: گزینه۱,گزینه₂">
                </div>
                <div class="flex items-end gap-3">
                    <label class="inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="schema_json[fields][${index}][required]" value="1">
                        ضروری
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="schema_json[fields][${index}][collect_from_online]" value="1">
                        دریافت از کاربر آنلاین
                    </label>
                    <button type="button" class="text-rose-600 dark:text-rose-300 text-xs font-medium hover:underline" onclick="this.closest('.form-field-row').remove()">حذف</button>
                </div>
            `;
            container.appendChild(row);
        });
    });
</script>
