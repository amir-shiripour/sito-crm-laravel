@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">ویرایش قانون قرارداد ساز</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">تغییر و به‌روزرسانی شرط‌ها و تنظیمات قانون «{{ $rule->name }}»</p>
            </div>
            <div>
                <a href="{{ route('user.contracts.rules.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    بازگشت به لیست
                </a>
            </div>
        </div>

        <form action="{{ route('user.contracts.rules.update', $rule->id) }}" method="POST" id="ruleForm">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Panel: Rule Info & Condition Builder -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                        <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">اطلاعات کلی قانون</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">نام قانون</label>
                                <input type="text" name="name" value="{{ $rule->name }}" required class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">قالب متصل</label>
                                <select name="template_id" required class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($templates as $tpl)
                                        <option value="{{ $tpl->id }}" {{ $rule->template_id == $tpl->id ? 'selected' : '' }}>{{ $tpl->name }} ({{ $tpl->entity_type }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">موجودیت مرتبط</label>
                                <select name="entity_type" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($entityTypes as $val => $lbl)
                                        <option value="{{ $val }}" {{ $rule->entity_type === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">رویداد فعال‌ساز</label>
                                <select name="trigger_event" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($events as $val => $lbl)
                                        <option value="{{ $val }}" {{ $rule->trigger_event === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Condition builder -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">شرط‌های ارزیابی (Expression Builder)</h2>
                                <p class="text-xs text-gray-500 mt-1">تعیین شرط‌های فیلترینگ داینامیک برای فیلدهای موجودیت</p>
                            </div>
                            <button type="button" onclick="addConditionRow()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
                                + افزودن شرط
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">ارزیابی شرط‌ها با عملگر:</span>
                                <select name="conditions[operator]" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-1 text-sm text-gray-900 dark:text-gray-100">
                                    <option value="AND" {{ ($rule->conditions['operator'] ?? 'AND') === 'AND' ? 'selected' : '' }}>AND (همه شرط‌ها برقرار باشند)</option>
                                    <option value="OR" {{ ($rule->conditions['operator'] ?? 'AND') === 'OR' ? 'selected' : '' }}>OR (حداقل یک شرط برقرار باشد)</option>
                                </select>
                            </div>

                            <div id="conditionsContainer" class="space-y-3">
                                <!-- JS will add condition rows here -->
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-200">
                                بروزرسانی قانون
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar: Additional settings -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-5">
                        <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">تنظیمات پیشرفته</h2>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">اولویت اجرا</label>
                            <input type="number" name="priority" value="{{ $rule->priority }}" required class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm" placeholder="اعداد بزرگتر اولویت بالاتری دارند">
                        </div>

                        <div class="space-y-4 pt-2">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100">جلوگیری از ایجاد تکراری</label>
                                    <p class="text-[10px] text-gray-400">فقط یک قرارداد برای هر موجودیت صادر شود</p>
                                </div>
                                <input type="checkbox" name="prevent_duplicate" value="1" {{ $rule->prevent_duplicate ? 'checked' : '' }} class="h-4.5 w-4.5 rounded border-gray-350 text-indigo-650 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">وضعیت‌های تحریک نوبت‌دهی (اختیاری)</label>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($statuses as $k => $v)
                                        @php
                                            $checked = is_array($rule->trigger_statuses) && in_array($k, $rule->trigger_statuses);
                                        @endphp
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 cursor-pointer">
                                            <input type="checkbox" name="trigger_statuses[]" value="{{ $k }}" {{ $checked ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                                            <span class="text-xs text-gray-700 dark:text-gray-300">{{ $v }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        let conditionCounter = 0;
        const clients = @json($clients);
        const statuses = @json($statuses);
        const paymentOptions = @json($paymentOptions);
        const sysCurrency = '{{ $sysCurrency }}';

        const availableFields = {
            'patient_name': 'نام بیمار',
            'plan_status': 'وضعیت طرح درمان',
            'installment_option_title': 'روش پرداخت (installment_option_title)',
            'plan_total': 'مبلغ کل طرح درمان',
            'plan_final_payable': 'مبلغ نهایی قابل پرداخت',
            'installment_months': 'تعداد ماه‌های اقساط'
        };

        let debounceTimer;
        window.searchPatients = function(input, rowNum) {
            const query = input.value;
            const dropdown = document.getElementById(`patient_dropdown_${rowNum}`);
            if (!dropdown) return;

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                dropdown.innerHTML = '<div class="p-2 text-xs text-gray-500">در حال جستجو...</div>';
                dropdown.classList.remove('hidden');

                fetch(`/user/contracts/rules/search-clients?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        dropdown.innerHTML = '';
                        if (data.length === 0) {
                            dropdown.innerHTML = '<div class="p-2 text-xs text-gray-500">بیماری یافت نشد.</div>';
                            return;
                        }
                        data.forEach(client => {
                            const option = document.createElement('div');
                            option.className = 'p-2.5 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 cursor-pointer text-xs border-b border-gray-100 dark:border-gray-800 last:border-0';
                            
                            let subDetails = [];
                            if (client.phone) subDetails.push(`تلفن: ${client.phone}`);
                            if (client.case_number) subDetails.push(`پرونده: ${client.case_number}`);
                            if (client.national_code) subDetails.push(`کدملی: ${client.national_code}`);
                            if (client.username) subDetails.push(`کاربری: ${client.username}`);
                            
                            const detailsStr = subDetails.length > 0 ? ` <span class="text-gray-400 text-[10px] font-mono">(${subDetails.join(' - ')})</span>` : '';
                            
                            option.innerHTML = `<div class="font-semibold text-gray-900 dark:text-gray-100">${client.full_name}</div>${detailsStr}`;
                            option.onclick = () => {
                                input.value = client.full_name;
                                document.getElementById(`hidden_patient_${rowNum}`).value = client.full_name;
                                dropdown.classList.add('hidden');
                            };
                            dropdown.appendChild(option);
                        });
                    })
                    .catch(err => {
                        dropdown.innerHTML = '<div class="p-2 text-xs text-rose-500 font-semibold">خطا در دریافت اطلاعات.</div>';
                    });
            }, 300);
        }

        window.formatPriceInput = function(input) {
            let value = input.value.replace(/,/g, '');
            value = value.replace(/\D/g, '');
            
            const rowNum = input.dataset.row;
            const hiddenInput = document.getElementById(`hidden_value_${rowNum}`);
            if (hiddenInput) {
                hiddenInput.value = value;
            }
            
            if (value) {
                input.value = Number(value).toLocaleString();
            } else {
                input.value = '';
            }
        }

        document.addEventListener('click', (e) => {
            document.querySelectorAll('.patient-dropdown-list').forEach(dropdown => {
                if (!dropdown.contains(e.target) && !dropdown.previousElementSibling.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        });

        function updateValueField(rowNum, selectedField, currentValue = '') {
            const container = document.getElementById(`value_container_${rowNum}`);
            if (!container) return;

            let html = '';
            if (selectedField === 'patient_name') {
                html = `
                    <div class="relative flex-1 min-w-[200px]">
                        <input type="hidden" id="hidden_patient_${rowNum}" name="conditions[rules][${rowNum}][value]" value="${currentValue || ''}">
                        <input type="text" value="${currentValue || ''}" onfocus="searchPatients(this, ${rowNum})" oninput="searchPatients(this, ${rowNum})" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-1.5 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400" placeholder="جستجوی بیمار (نام، تلفن، کدملی...)">
                        <div id="patient_dropdown_${rowNum}" class="patient-dropdown-list hidden absolute z-50 left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl"></div>
                    </div>
                `;
            } else if (selectedField === 'plan_status') {
                html = `<select name="conditions[rules][${rowNum}][value]" class="flex-1 min-w-[150px] rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-1.5 text-xs text-gray-900 dark:text-gray-100">`;
                html += `<option value="">انتخاب وضعیت...</option>`;
                for (const [k, v] of Object.entries(statuses)) {
                    html += `<option value="${v}" ${currentValue === v ? 'selected' : ''}>${v}</option>`;
                }
                html += `</select>`;
            } else if (selectedField === 'installment_option_title') {
                html = `<select name="conditions[rules][${rowNum}][value]" class="flex-1 min-w-[150px] rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-1.5 text-xs text-gray-900 dark:text-gray-100">`;
                html += `<option value="">انتخاب روش پرداخت...</option>`;
                paymentOptions.forEach(opt => {
                    html += `<option value="${opt}" ${currentValue === opt ? 'selected' : ''}>${opt}</option>`;
                });
                html += `</select>`;
            } else if (selectedField === 'plan_total' || selectedField === 'plan_final_payable') {
                const cleanValue = currentValue ? String(currentValue).replace(/,/g, '') : '';
                const formattedValue = cleanValue ? Number(cleanValue).toLocaleString() : '';
                html = `
                    <div class="flex items-center gap-1.5 flex-1 min-w-[200px] relative">
                        <input type="hidden" id="hidden_value_${rowNum}" name="conditions[rules][${rowNum}][value]" value="${cleanValue}">
                        <input type="text" data-row="${rowNum}" value="${formattedValue}" oninput="formatPriceInput(this)" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-1.5 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 font-mono text-left" placeholder="مثال: 50,000,000">
                        <span class="text-xs text-gray-500 font-bold whitespace-nowrap">${sysCurrency}</span>
                    </div>
                `;
            } else {
                html = `<input type="text" name="conditions[rules][${rowNum}][value]" value="${currentValue || ''}" class="flex-1 min-w-[150px] rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-1.5 text-xs text-gray-900 dark:text-gray-100" placeholder="مقدار مقایسه">`;
            }

            container.innerHTML = html;
        }

        function addConditionRow(data = {}) {
            conditionCounter++;
            const container = document.getElementById('conditionsContainer');
            
            const row = document.createElement('div');
            row.className = 'flex flex-wrap items-center gap-3 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-800';
            row.id = `condition_row_${conditionCounter}`;

            let fieldsHtml = `<select name="conditions[rules][${conditionCounter}][field]" onchange="updateValueField(${conditionCounter}, this.value)" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-1.5 text-xs text-gray-900 dark:text-gray-100">`;
            for (const [k, v] of Object.entries(availableFields)) {
                fieldsHtml += `<option value="${k}" ${data.field === k ? 'selected' : ''}>${v}</option>`;
            }
            fieldsHtml += `</select>`;

            row.innerHTML = `
                ${fieldsHtml}
                
                <select name="conditions[rules][${conditionCounter}][op]" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-1.5 text-xs text-gray-900 dark:text-gray-100">
                    <option value="equals" ${data.op === 'equals' ? 'selected' : ''}>برابر باشد با</option>
                    <option value="not_equals" ${data.op === 'not_equals' ? 'selected' : ''}>مخالف باشد با</option>
                    <option value="contains" ${data.op === 'contains' ? 'selected' : ''}>شامل شود</option>
                    <option value="gt" ${data.op === 'gt' ? 'selected' : ''}>بزرگتر از</option>
                    <option value="lt" ${data.op === 'lt' ? 'selected' : ''}>کوچکتر از</option>
                    <option value="is_null" ${data.op === 'is_null' ? 'selected' : ''}>خالی باشد</option>
                    <option value="is_not_null" ${data.op === 'is_not_null' ? 'selected' : ''}>پر باشد</option>
                </select>

                <div id="value_container_${conditionCounter}" class="flex-1 flex items-center min-w-[150px]"></div>

                <button type="button" onclick="removeConditionRow('${row.id}')" class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            `;

            container.appendChild(row);
            
            const fieldVal = data.field || Object.keys(availableFields)[0];
            updateValueField(conditionCounter, fieldVal, data.value);
        }

        function removeConditionRow(id) {
            const row = document.getElementById(id);
            if (row) row.remove();
        }

        // Load existing conditions from the rule database record
        window.addEventListener('DOMContentLoaded', () => {
            const ruleData = @json($rule->conditions['rules'] ?? []);
            if (Object.keys(ruleData).length > 0) {
                Object.values(ruleData).forEach(cond => {
                    addConditionRow(cond);
                });
            } else {
                addConditionRow();
            }
        });
    </script>
@endsection
