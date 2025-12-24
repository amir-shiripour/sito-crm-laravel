
@php
    $action = $action ?? '';
    $triggerOptions = $triggerOptions ?? [];
    $appointmentOptions = $triggerOptions['APPOINTMENT'] ?? [];
    $knownAppointmentKeys = array_keys($appointmentOptions);

    $currentKey = old('key', $workflow->key ?? '');
    $presetFromRequest = old('key_preset');

    if ($presetFromRequest !== null) {
        $preset = $presetFromRequest;
    } else {
        $preset = in_array($currentKey, $knownAppointmentKeys, true) ? $currentKey : '__custom__';
    }
@endphp

<form method="post" action="{{ $action }}" class="space-y-4">
    @csrf
    @if(($method ?? '') === 'patch')
        @method('patch')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">نام گردش کار</label>
            <input type="text" name="name" value="{{ old('name', $workflow->name ?? '') }}"
                   class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm text-gray-900
                          focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition
                          dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">تریگر</label>
            <select name="key_preset" id="wf-key-preset"
                    class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm text-gray-900
                           focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition
                           dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                <option value="">انتخاب کنید...</option>

                @if(!empty($appointmentOptions))
                    <optgroup label="نوبت‌ها (APPOINTMENT)">
                        @foreach($appointmentOptions as $k => $label)
                            <option value="{{ $k }}" @selected($preset === $k)>{{ $label }} ({{ $k }})</option>
                        @endforeach
                    </optgroup>
                @endif

                <option value="__custom__" @selected($preset === '__custom__')>کلید دلخواه</option>
            </select>

            <p class="text-xs text-gray-500 mt-1">برای جلوگیری از خطا، پیشنهاد می‌شود از لیست تریگرها انتخاب کنید.</p>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">کلید (key)</label>
        <input type="text" name="key" id="wf-key" value="{{ $currentKey }}"
               class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm text-gray-900
                      focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition
                      dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
        <p class="text-xs text-gray-500 mt-1">این کلید در فراخوانی کد و Webhook ها استفاده می‌شود.</p>

        <script>
            (function () {
                const sel = document.getElementById('wf-key-preset');
                const keyInput = document.getElementById('wf-key');
                if (!sel || !keyInput) return;

                function setEditable(isEditable) {
                    if (isEditable) {
                        keyInput.removeAttribute('readonly');
                    } else {
                        keyInput.setAttribute('readonly', 'readonly');
                    }
                }

                function sync() {
                    const v = sel.value;

                    // اگر چیزی انتخاب نشده، key باید قابل ویرایش باشد
                    if (!v) {
                        setEditable(true);
                        return;
                    }

                    // کلید دلخواه
                    if (v === '__custom__') {
                        setEditable(true);
                        return;
                    }

                    // تریگر انتخابی
                    keyInput.value = v;
                    setEditable(false);
                }

                sel.addEventListener('change', sync);

                // init
                sync();
            })();
        </script>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">توضیحات</label>
        <textarea name="description" rows="3"
                  class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm text-gray-900
                         focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition
                         dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('description', $workflow->description ?? '') }}</textarea>
    </div>

    <div class="flex items-center gap-3">
        <input type="hidden" name="is_active" value="0">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $workflow->is_active ?? true))
            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm text-gray-700 dark:text-gray-200">فعال باشد</span>
        </label>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl shadow hover:bg-indigo-700 transition">
            ذخیره
        </button>

        @if(($method ?? '') === 'patch')
            <a href="{{ route('user.workflows.show', $workflow) }}"
               class="text-sm text-gray-600 hover:text-indigo-600 dark:text-gray-300">مشاهده</a>
        @endif
    </div>
</form>
