@php
    $action = $action ?? '';
    // Load existing triggers or default to one empty trigger
    $triggers = old('triggers', isset($workflow) ? $workflow->triggers->toArray() : []);
    if (empty($triggers)) {
        $triggers = [['type' => '', 'config' => []]];
    }
@endphp

<form method="post" action="{{ $action }}" class="space-y-8">
    @csrf
    @if(($method ?? '') === 'patch')
        @method('patch')
    @endif

    <!-- بخش اطلاعات پایه -->
    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            اطلاعات کلی گردش کار
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- نام -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">نام گردش کار <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="wf-name" value="{{ old('name', $workflow->name ?? '') }}"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                              dark:bg-gray-900 dark:border-gray-600 dark:text-white dark:focus:ring-indigo-500/50"
                       placeholder="مثال: ارسال پیامک تایید نوبت" required>
                <p class="text-xs text-gray-500">یک نام گویا برای این فرآیند انتخاب کنید.</p>
            </div>

            <!-- کلید (پیشرفته) -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    شناسه سیستمی (Key)
                    <span class="text-xs font-normal text-gray-400">(تولید خودکار)</span>
                </label>
                <div class="relative rounded-md shadow-sm">
                    <input type="text" name="key" id="wf-key" value="{{ old('key', $workflow->key ?? '') }}"
                           class="block w-full rounded-md border-gray-300 bg-gray-50 text-gray-500 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                  dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400"
                           required>
                </div>
                <p class="text-xs text-gray-500">شناسه یکتا برای سیستم (انگلیسی).</p>
            </div>

            <!-- توضیحات -->
            <div class="col-span-1 md:col-span-2 space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">توضیحات (اختیاری)</label>
                <textarea name="description" rows="2"
                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                 dark:bg-gray-900 dark:border-gray-600 dark:text-white dark:focus:ring-indigo-500/50"
                          placeholder="یادداشتی برای مدیران سیستم...">{{ old('description', $workflow->description ?? '') }}</textarea>
            </div>

            <!-- وضعیت -->
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-md border border-gray-100 dark:border-gray-700">
                    <input type="hidden" name="is_active" value="0">
                    <div class="flex items-center h-5">
                        <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $workflow->is_active ?? true))
                               class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 cursor-pointer">
                    </div>
                    <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300 select-none cursor-pointer">
                        این گردش کار فعال باشد
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- بخش تریگرها -->
    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 border border-gray-200 dark:border-gray-700 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>

        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    شرایط شروع (Triggers)
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">مشخص کنید این گردش کار چه زمانی باید اجرا شود.</p>
            </div>

            <button type="button" id="add-trigger-btn"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-indigo-900 dark:text-indigo-200 dark:hover:bg-indigo-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                افزودن شرط جدید
            </button>
        </div>

        <div id="triggers-container" class="space-y-4">
            @foreach($triggers as $index => $trigger)
                <div class="trigger-item group relative bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700 p-5 transition-all hover:border-indigo-300 dark:hover:border-indigo-700">

                    <!-- دکمه حذف -->
                    @if($index > 0 || count($triggers) > 1)
                    <button type="button" class="remove-trigger absolute top-4 left-4 text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                        <!-- انتخاب نوع -->
                        <div class="md:col-span-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">نوع شرط</label>
                            <select name="triggers[{{ $index }}][type]" class="trigger-type-select block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2.5">
                                <option value="">انتخاب کنید...</option>
                                <optgroup label="بر اساس رویداد (آنی)">
                                    <option value="EVENT" @selected(($trigger['type'] ?? '') === 'EVENT')>وقتی اتفاقی می‌افتد (Event)</option>
                                    <option value="APPOINTMENT_STATUS" @selected(($trigger['type'] ?? '') === 'APPOINTMENT_STATUS')>وقتی وضعیت نوبت تغییر کرد</option>
                                </optgroup>
                                <optgroup label="بر اساس زمان (زمان‌بندی)">
                                    <option value="APPOINTMENT_REMINDER" @selected(($trigger['type'] ?? '') === 'APPOINTMENT_REMINDER')>یادآوری نوبت (قبل/بعد از زمان نوبت)</option>
                                    <option value="SCHEDULE" @selected(($trigger['type'] ?? '') === 'SCHEDULE')>زمان‌بندی خاص (Cron Job)</option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- تنظیمات مربوطه -->
                        <div class="md:col-span-8 border-r-2 border-gray-200 dark:border-gray-700 pr-6 mr-2">

                            <!-- راهنما -->
                            <div class="trigger-config hidden text-sm text-gray-500 flex items-center h-full">
                                <span class="italic">لطفاً ابتدا نوع شرط را انتخاب کنید.</span>
                            </div>

                            <!-- 1. EVENT -->
                            <div class="trigger-config config-EVENT {{ ($trigger['type'] ?? '') === 'EVENT' ? '' : 'hidden' }}">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">کدام رویداد؟</label>
                                <select name="triggers[{{ $index }}][config][event_key]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                    <option value="">انتخاب رویداد...</option>
                                    @if(isset($triggerOptions['APPOINTMENT']))
                                        @foreach($triggerOptions['APPOINTMENT'] as $key => $label)
                                            <option value="{{ $key }}" @selected(($trigger['config']['event_key'] ?? '') === $key)>{{ $label }}</option>
                                        @endforeach
                                    @else
                                        <option value="appointment_created" @selected(($trigger['config']['event_key'] ?? '') === 'appointment_created')>ایجاد نوبت جدید (توسط هر کسی)</option>
                                        <option value="appointment_created_online" @selected(($trigger['config']['event_key'] ?? '') === 'appointment_created_online')>رزرو آنلاین نوبت (توسط مشتری)</option>
                                        <option value="appointment_created_operator" @selected(($trigger['config']['event_key'] ?? '') === 'appointment_created_operator')>ثبت نوبت توسط اپراتور</option>
                                        <option value="appointment_canceled" @selected(($trigger['config']['event_key'] ?? '') === 'appointment_canceled')>لغو نوبت</option>
                                        <option value="appointment_no_show" @selected(($trigger['config']['event_key'] ?? '') === 'appointment_no_show')>عدم حضور مشتری (No-Show)</option>
                                    @endif
                                </select>
                                <p class="text-xs text-gray-500 mt-2">به محض رخ دادن این اتفاق، گردش کار اجرا می‌شود.</p>
                            </div>

                            <!-- 2. APPOINTMENT_REMINDER -->
                            <div class="trigger-config config-APPOINTMENT_REMINDER {{ ($trigger['type'] ?? '') === 'APPOINTMENT_REMINDER' ? '' : 'hidden' }}">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">زمان اجرا</label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" name="triggers[{{ $index }}][config][offset_minutes]" value="{{ $trigger['config']['offset_minutes'] ?? -60 }}"
                                                   class="block w-full rounded-md border-gray-300 pr-2 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white text-left" dir="ltr">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">دقیقه</span>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">منفی (-) = قبل از نوبت | مثبت (+) = بعد از نوبت</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">وضعیت نوبت</label>
                                        <select name="triggers[{{ $index }}][config][status]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                            <option value="CONFIRMED" @selected(($trigger['config']['status'] ?? '') === 'CONFIRMED')>تایید شده (CONFIRMED)</option>
                                            <option value="PENDING_PAYMENT" @selected(($trigger['config']['status'] ?? '') === 'PENDING_PAYMENT')>در انتظار پرداخت</option>
                                            <option value="DONE" @selected(($trigger['config']['status'] ?? '') === 'DONE')>انجام شده</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. APPOINTMENT_STATUS -->
                            <div class="trigger-config config-APPOINTMENT_STATUS {{ ($trigger['type'] ?? '') === 'APPOINTMENT_STATUS' ? '' : 'hidden' }}">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">تغییر به وضعیت:</label>
                                <select name="triggers[{{ $index }}][config][status]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                    <option value="CONFIRMED" @selected(($trigger['config']['status'] ?? '') === 'CONFIRMED')>تایید شده (CONFIRMED)</option>
                                    <option value="CANCELED_BY_CLIENT" @selected(($trigger['config']['status'] ?? '') === 'CANCELED_BY_CLIENT')>لغو توسط مشتری</option>
                                    <option value="CANCELED_BY_ADMIN" @selected(($trigger['config']['status'] ?? '') === 'CANCELED_BY_ADMIN')>لغو توسط ادمین</option>
                                    <option value="DONE" @selected(($trigger['config']['status'] ?? '') === 'DONE')>انجام شده</option>
                                    <option value="NO_SHOW" @selected(($trigger['config']['status'] ?? '') === 'NO_SHOW')>عدم حضور (No-Show)</option>
                                </select>
                            </div>

                            <!-- 4. SCHEDULE -->
                            <div class="trigger-config config-SCHEDULE {{ ($trigger['type'] ?? '') === 'SCHEDULE' ? '' : 'hidden' }}">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cron Expression</label>
                                <input type="text" name="triggers[{{ $index }}][config][cron]" value="{{ $trigger['config']['cron'] ?? '* * * * *' }}"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white font-mono text-left" dir="ltr"
                                       placeholder="* * * * *">
                                <p class="text-xs text-gray-500 mt-2">
                                    فرمت استاندارد کرون (دقیقه ساعت روز ماه هفته). <br>
                                    مثال: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">0 8 * * *</code> (هر روز ساعت 8 صبح)
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- دکمه‌های عملیات -->
    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
        @if(($method ?? '') === 'patch')
            <a href="{{ route('user.workflows.index') }}"
               class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
                انصراف
            </a>
        @endif

        <button type="submit"
                class="inline-flex justify-center px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-colors">
            {{ ($method ?? '') === 'patch' ? 'ذخیره تغییرات' : 'ایجاد گردش کار' }}
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Auto-Generate Key Logic ---
        const nameInput = document.getElementById('wf-name');
        const keyInput = document.getElementById('wf-key');

        // Only auto-generate if key is empty or looks like a slug of the name
        // But to keep it simple and user-friendly:
        // If creating new (key is empty), auto-fill.
        // If editing, don't touch unless user clears it.

        if (nameInput && keyInput) {
            nameInput.addEventListener('input', function() {
                // Only if key is empty or we are in "create" mode (roughly detected by empty key initially)
                // Or simply: if the user hasn't manually edited the key yet.
                // Let's just do: if key is empty or matches the slug of previous name state.
                // Simpler approach: If key is not readonly (it is editable), update it.

                // Slugify: Persian/English friendly
                // 1. Trim
                // 2. Replace spaces with _
                // 3. Keep only letters, numbers, _, -
                let slug = nameInput.value.trim()
                    .replace(/\s+/g, '_')
                    .replace(/[^a-zA-Z0-9_\-\u0600-\u06FF]/g, '') // Allow Persian chars too if you want, but usually keys are EN
                    // For strict English keys:
                    .replace(/[^a-zA-Z0-9_\-]/g, '')
                    .toLowerCase();

                // If key is empty or user is typing, update it.
                // We check if the key field has been "touched" manually?
                // For now, let's just update if key is empty OR if it looks like an auto-generated slug.
                if (keyInput.value === '' || keyInput.dataset.auto === 'true') {
                    keyInput.value = slug;
                    keyInput.dataset.auto = 'true';
                }
            });

            // If user manually edits key, stop auto-updating
            keyInput.addEventListener('input', function() {
                keyInput.dataset.auto = 'false';
            });

            // Initialize dataset
            if (keyInput.value === '') keyInput.dataset.auto = 'true';
        }

        // --- Triggers Logic ---
        const container = document.getElementById('triggers-container');
        const addBtn = document.getElementById('add-trigger-btn');

        function handleTypeChange(select) {
            const item = select.closest('.trigger-item');
            const type = select.value;

            // Hide all configs
            item.querySelectorAll('.trigger-config').forEach(el => el.classList.add('hidden'));

            // Show selected config
            if (type) {
                const configDiv = item.querySelector(`.config-${type}`);
                if (configDiv) configDiv.classList.remove('hidden');
            } else {
                // Show placeholder if nothing selected?
                // item.querySelector('.trigger-config:not([class*="config-"])').classList.remove('hidden');
            }
        }

        // Initial setup
        container.querySelectorAll('.trigger-type-select').forEach(handleTypeChange);

        // Event Delegation
        container.addEventListener('change', function(e) {
            if (e.target.classList.contains('trigger-type-select')) {
                handleTypeChange(e.target);
            }
        });

        // Add Trigger
        addBtn.addEventListener('click', function() {
            const index = container.children.length;
            // Clone the first item (or a template if we had one)
            // If container is empty (shouldn't happen due to PHP logic), we'd need a template.
            // Assuming PHP always renders at least one item or we clone the last one.

            let template = container.firstElementChild;
            if (!template) return; // Should not happen

            const newItem = template.cloneNode(true);

            // Reset inputs
            newItem.querySelectorAll('input, select').forEach(input => {
                // Reset value
                if (input.tagName === 'SELECT') input.selectedIndex = 0;
                else input.value = '';

                // Update name index
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
                }

                // Defaults
                if (input.name.includes('[offset_minutes]')) input.value = '-60';
                if (input.name.includes('[cron]')) input.value = '* * * * *';
            });

            // Ensure remove button exists
            if (!newItem.querySelector('.remove-trigger')) {
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'remove-trigger absolute top-4 left-4 text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20';
                removeBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>';
                newItem.appendChild(removeBtn);
            }

            container.appendChild(newItem);
            handleTypeChange(newItem.querySelector('.trigger-type-select'));
        });

        // Remove Trigger
        container.addEventListener('click', function(e) {
            const btn = e.target.closest('.remove-trigger');
            if (btn) {
                if (container.children.length > 1) {
                    btn.closest('.trigger-item').remove();
                } else {
                    alert('حداقل یک شرط برای شروع گردش کار لازم است.');
                }
            }
        });
    });
</script>
