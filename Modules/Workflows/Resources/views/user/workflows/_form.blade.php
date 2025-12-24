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

<form method="post" action="{{ $action }}" class="space-y-6">
    @csrf
    @if(($method ?? '') === 'patch')
        @method('patch')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">نام گردش کار</label>
            <input type="text" name="name" value="{{ old('name', $workflow->name ?? '') }}"
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                          dark:bg-gray-900 dark:border-gray-600 dark:text-white dark:focus:ring-indigo-500/50"
                   placeholder="مثال: یادآوری نوبت" required>
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">تریگر (رویداد)</label>
            <select name="key_preset" id="wf-key-preset"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                           dark:bg-gray-900 dark:border-gray-600 dark:text-white dark:focus:ring-indigo-500/50">
                <option value="">انتخاب کنید...</option>

                @if(!empty($appointmentOptions))
                    <optgroup label="نوبت‌ها (APPOINTMENT)">
                        @foreach($appointmentOptions as $k => $label)
                            <option value="{{ $k }}" @selected($preset === $k)>{{ $label }} ({{ $k }})</option>
                        @endforeach
                    </optgroup>
                @endif

                <option value="__custom__" @selected($preset === '__custom__')>کلید دلخواه / دستی</option>
            </select>
            <p class="text-xs text-gray-500 dark:text-gray-400">رویدادی که باعث شروع این گردش کار می‌شود را انتخاب کنید.</p>
        </div>
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">کلید سیستمی (Key)</label>
        <div class="relative rounded-md shadow-sm">
            <input type="text" name="key" id="wf-key" value="{{ $currentKey }}"
                   class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                          dark:bg-gray-900 dark:border-gray-600 dark:text-white dark:focus:ring-indigo-500/50 read-only:bg-gray-100 dark:read-only:bg-gray-800 read-only:text-gray-500"
                   required>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">این کلید شناسه منحصر‌به‌فرد گردش کار است و در کد استفاده می‌شود.</p>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const sel = document.getElementById('wf-key-preset');
                const keyInput = document.getElementById('wf-key');
                if (!sel || !keyInput) return;

                function setEditable(isEditable) {
                    if (isEditable) {
                        keyInput.removeAttribute('readonly');
                        keyInput.classList.remove('bg-gray-100', 'text-gray-500', 'dark:bg-gray-800');
                    } else {
                        keyInput.setAttribute('readonly', 'readonly');
                        keyInput.classList.add('bg-gray-100', 'text-gray-500', 'dark:bg-gray-800');
                    }
                }

                function sync() {
                    const v = sel.value;
                    if (!v || v === '__custom__') {
                        setEditable(true);
                        if (!v) keyInput.value = '';
                        return;
                    }
                    keyInput.value = v;
                    setEditable(false);
                }

                sel.addEventListener('change', sync);
                sync();
            });
        </script>
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">توضیحات</label>
        <textarea name="description" rows="3"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                         dark:bg-gray-900 dark:border-gray-600 dark:text-white dark:focus:ring-indigo-500/50"
                  placeholder="توضیحات اختیاری در مورد عملکرد این گردش کار...">{{ old('description', $workflow->description ?? '') }}</textarea>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <input type="hidden" name="is_active" value="0">
        <div class="flex items-center h-5">
            <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $workflow->is_active ?? true))
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800">
        </div>
        <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300 select-none">
            گردش کار فعال باشد
        </label>
    </div>

    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
        @if(($method ?? '') === 'patch')
            <a href="{{ route('user.workflows.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                انصراف
            </a>
        @endif

        <button type="submit"
                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
            {{ ($method ?? '') === 'patch' ? 'ذخیره تغییرات' : 'ایجاد گردش کار' }}
        </button>
    </div>
</form>
