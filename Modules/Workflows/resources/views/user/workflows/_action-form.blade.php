@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $actionInstance = $action ?? null;
    $cfg = $isEdit ? ($actionInstance->config ?? []) : [];
    $formAction = $isEdit
        ? route('user.workflows.actions.update', [$workflow, $stage, $actionInstance])
        : route('user.workflows.actions.store', [$workflow, $stage]);

    $alpineId = $isEdit ? 'action_edit_' . $actionInstance->id : 'action_create_' . $stage->id;

    $types = [
        \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS => 'ارسال پیامک (SMS)',
        \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK => 'ایجاد وظیفه (Task)',
        \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP => 'ایجاد پیگیری (FollowUp)',
        \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_NOTIFICATION => 'نوتیفیکیشن سیستم',
    ];

    // Default Tokens
    $defaultTokens = [
        'appointment' => [
            'client_name' => 'نام مشتری',
            'client_phone' => 'شماره مشتری',
            'service_name' => 'نام سرویس',
            'provider_name' => 'نام پزشک',
            'appointment_date_jalali' => 'تاریخ نوبت (شمسی)',
            'appointment_time_jalali' => 'ساعت نوبت',
            'appointment_datetime_jalali' => 'تاریخ و ساعت کامل',
            'payment_link' => 'لینک پرداخت',
        ],
        'treatment_plan' => [
            'plan_id' => 'شناسه طرح درمان',
            'patient_name' => 'نام بیمار',
            'status' => 'شناسه وضعیت',
            'status_label' => 'نام وضعیت طرح درمان',
            'total' => 'مبلغ کل',
            'final_payable' => 'مبلغ قابل پرداخت',
            'currency' => 'واحد پول',
            'client_phone' => 'شماره بیمار',
            'creator_name' => 'ثبت کننده طرح',
            'creator_phone' => 'تلفن ثبت کننده',
        ],
        'client' => [
            'client_id' => 'شناسه بیمار/کلاینت',
            'client_name' => 'نام بیمار',
            'client_username' => 'نام کاربری',
            'client_phone' => 'شماره بیمار',
            'client_email' => 'ایمیل بیمار',
            'client_national_code' => 'کد ملی بیمار',
            'client_case_number' => 'شماره پرونده',
            'client_notes' => 'یادداشت پرونده',
            'client_status' => 'وضعیت پرونده',
            'client_created_at_jalali' => 'تاریخ ایجاد پرونده (شمسی)',
            'client_creator_name' => 'نام ثبت‌کننده پرونده',
        ]
    ];

    if (class_exists(\Modules\Clients\Entities\ClientForm::class)) {
        $clientForm = \Modules\Clients\Entities\ClientForm::default();
        if ($clientForm) {
            $fields = $clientForm->schema['fields'] ?? [];
            foreach ($fields as $field) {
                $fieldId = $field['id'] ?? null;
                $label = $field['label'] ?? $fieldId;
                if ($fieldId && !\Modules\Clients\Entities\ClientForm::isSystemFieldId($fieldId)) {
                    $defaultTokens['client']["client_custom_{$fieldId}"] = $label . ' (فیلد سفارشی)';
                }
            }
        }
    }

    if (isset($cureRoles)) {
        foreach ($cureRoles as $role) {
            $roleSlug = preg_replace('/[^a-zA-Z0-9_\x7f-\xff]/u', '_', $role->name);
            $roleSlug = trim(preg_replace('/_+/', '_', $roleSlug), '_');
            if (empty($roleSlug)) {
                $roleSlug = 'role_' . $role->id;
            }
            $defaultTokens['treatment_plan']["plan_role_{$roleSlug}_name"] = "نام «{$role->name}»";
            $defaultTokens['treatment_plan']["plan_role_{$roleSlug}_phone"] = "تلفن «{$role->name}»";
            $defaultTokens['treatment_plan']["plan_role_{$roleSlug}_all_names"] = "همه «{$role->name}»ها";
        }
    }

    // Merge with tokens from config
    $configTokens = config('workflows.tokens', []);

    // Manual merge to ensure structure
    $groupedTokens = $defaultTokens;
    foreach ($configTokens as $group => $tokens) {
        if (!isset($groupedTokens[$group])) {
            $groupedTokens[$group] = [];
        }
        foreach ($tokens as $key => $label) {
            $groupedTokens[$group][$key] = $label;
        }
    }
@endphp

<div x-data="{
         actionType: '{{ $isEdit ? $actionInstance->action_type : \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}',
         assigneeTarget: '{{ $cfg['assignee_target'] ?? 'CURRENT_USER' }}',
         smsTarget: '{{ $cfg['target'] ?? 'APPOINTMENT_CLIENT' }}',
         notificationTarget: '{{ $cfg['notification_target'] ?? 'CURRENT_USER' }}',
         isOpen: false,

         insertToken(token) {
             const textarea = this.actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'
                 ? this.$refs.smsTextarea
                 : this.$refs.notificationTextarea;

             if (!textarea) return;

             const start = textarea.selectionStart;
             const end = textarea.selectionEnd;
             const text = textarea.value;
             const before = text.substring(0, start);
             const after = text.substring(end, text.length);

             textarea.value = before + '{' + token + '}' + after;
             textarea.selectionStart = textarea.selectionEnd = start + token.length + 2;
             textarea.focus();
         }
     }"
     id="{{ $alpineId }}"
     class="relative">

    {{-- 1. VIEW MODE --}}
    @if($isEdit)
        @php
            // Action visual styles based on type
            $styleMap = [
                \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS => [
                    'border' => 'border-emerald-100 hover:border-emerald-300 dark:border-emerald-900/30 dark:hover:border-emerald-800',
                    'bg' => 'bg-emerald-50/20 dark:bg-emerald-950/10',
                    'icon_bg' => 'bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400',
                ],
                \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK => [
                    'border' => 'border-blue-100 hover:border-blue-300 dark:border-blue-900/30 dark:hover:border-blue-800',
                    'bg' => 'bg-blue-50/20 dark:bg-blue-950/10',
                    'icon_bg' => 'bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400',
                ],
                \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP => [
                    'border' => 'border-purple-100 hover:border-purple-300 dark:border-purple-900/30 dark:hover:border-purple-800',
                    'bg' => 'bg-purple-50/20 dark:bg-purple-950/10',
                    'icon_bg' => 'bg-purple-50 dark:bg-purple-950 text-purple-600 dark:text-purple-400',
                ],
                \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_NOTIFICATION => [
                    'border' => 'border-amber-100 hover:border-amber-300 dark:border-amber-900/30 dark:hover:border-amber-800',
                    'bg' => 'bg-amber-50/20 dark:bg-amber-950/10',
                    'icon_bg' => 'bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400',
                ],
            ];
            $typeStyle = $styleMap[$actionInstance->action_type] ?? [
                'border' => 'border-gray-200 dark:border-gray-700',
                'bg' => 'bg-white dark:bg-gray-800',
                'icon_bg' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
            ];
        @endphp

        <div class="premium-stage-card border {{ $typeStyle['border'] }} {{ $typeStyle['bg'] }} rounded-xl p-3.5 flex items-center justify-between gap-4 transition-all">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="flex-shrink-0 w-9 h-9 rounded-xl {{ $typeStyle['icon_bg'] }} flex items-center justify-center shadow-sm">
                    @if($actionInstance->action_type === \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                    @elseif(in_array($actionInstance->action_type, [\Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK, \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP]))
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    @endif
                </div>

                <div class="min-w-0">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-150 truncate">
                        {{ $types[$actionInstance->action_type] ?? $actionInstance->action_type }}
                        @if(!empty($cfg['title']))
                            <span class="text-gray-500 dark:text-gray-400 font-medium text-xs">({{ $cfg['title'] }})</span>
                        @endif
                    </h4>
                    <p class="text-xs text-gray-400 dark:text-gray-400 truncate mt-0.5">
                        @if($actionInstance->action_type === \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS)
                            {{ Str::limit($cfg['message'] ?? ($cfg['pattern_key'] ? "الگو: {$cfg['pattern_key']}" : ''), 65) }}
                        @else
                            {{ Str::limit($cfg['description'] ?? '', 65) }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-shrink-0">
                <span class="text-[10px] text-gray-400 font-extrabold bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-lg border border-gray-200/50 dark:border-gray-750">#{{ $actionInstance->sort_order }}</span>
                <button type="button" @click="isOpen = !isOpen" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-850">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- 2. CREATE BUTTON --}}
    @if(!$isEdit)
        <button type="button" @click="isOpen = !isOpen" x-show="!isOpen"
                class="inline-flex items-center px-4 py-2.5 text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 border-dashed rounded-xl hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-indigo-950/20 dark:text-indigo-400 dark:border-indigo-900/30 dark:hover:bg-indigo-950/30 transition-all w-full justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            افزودن عملیات جدید
        </button>
    @endif

    {{-- 3. FORM --}}
    <div x-show="isOpen" x-transition
         class="{{ $isEdit ? 'mt-3' : '' }} bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 space-y-4 relative shadow-sm">

        <button type="button" @click="isOpen = false" class="absolute top-4 left-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>

        <form method="post" action="{{ $formAction }}" class="space-y-5 pt-2">
            @csrf
            @if($isEdit)
                @method('patch')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-8 space-y-1">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">نوع عملیات (Action Type)</label>
                    <select name="action_type" x-model="actionType"
                            class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2.5 px-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm">
                        @foreach($types as $type => $label)
                            <option value="{{ $type }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4 space-y-1">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">ترتیب اجرا</label>
                    <input type="number" name="sort_order" value="{{ $isEdit ? $actionInstance->sort_order : 0 }}" min="0"
                           class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2.5 px-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm">
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700 border-dashed">

            {{-- SMS Fields --}}
            <div class="space-y-5" x-show="actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Target Selection --}}
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">هدف ارسال (گیرنده)</label>
                        <select name="config[target]" x-model="smsTarget"
                                :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                                class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2.5 px-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm">
                            <option value="APPOINTMENT_CLIENT">بیمار نوبت</option>
                            <option value="APPOINTMENT_PROVIDER">پزشک نوبت</option>
                            <option value="STATEMENT_PROVIDER">ارائه‌دهنده صورت وضعیت</option>
                            <option value="SPECIFIC_USER">کاربر خاص سیستم</option>
                            <option value="CUSTOM_PHONE">شماره دلخواه</option>
                            <optgroup label="طرح درمان">
                                <option value="TREATMENT_PLAN_CLIENT">بیمار طرح درمان</option>
                                <option value="TREATMENT_PLAN_CREATOR">ایجادکننده طرح درمان</option>
                                @if(isset($cureRoles))
                                    @foreach($cureRoles as $role)
                                        <option value="TREATMENT_PLAN_ROLE_{{ $role->id }}">نقش «{{ $role->name }}» در طرح درمان</option>
                                    @endforeach
                                @endif
                            </optgroup>
                            <optgroup label="پرونده کلاینت">
                                <option value="CLIENT">بیمار پرونده</option>
                                <option value="CLIENT_CREATOR">ایجادکننده پرونده کلاینت</option>
                            </optgroup>
                            <optgroup label="تماس کلاینت">
                                <option value="CALL_CREATOR">ثبت‌کننده تماس</option>
                            </optgroup>
                            <optgroup label="وظیفه و پیگیری">
                                <option value="TASK_CREATOR">ایجادکننده وظیفه/پیگیری</option>
                                <option value="TASK_ASSIGNEE">ارجاع‌شونده وظیفه/پیگیری</option>
                            </optgroup>
                        </select>

                        {{-- Dynamic Target Inputs --}}
                        <div class="mt-2 space-y-2">
                            <div x-show="smsTarget === 'CUSTOM_PHONE'">
                                <input type="text" name="config[phone]" value="{{ $cfg['phone'] ?? '' }}" placeholder="شماره موبایل (مثلاً 0912...)"
                                       :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                                       class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2 px-3 text-sm">
                            </div>
                            <div x-show="smsTarget === 'SPECIFIC_USER'">
                                <select name="config[target_user_id]"
                                        :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                                        class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2 px-3 text-sm">
                                    <option value="">انتخاب کاربر...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected(($cfg['target_user_id'] ?? '') == $user->id)>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Offset --}}
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">تاخیر در ارسال پیامک</label>
                        <div class="flex rounded-xl border border-gray-300 dark:border-gray-700 overflow-hidden shadow-sm bg-white dark:bg-gray-900">
                            <input type="number" name="config[offset_minutes]" value="{{ $cfg['offset_minutes'] ?? '0' }}"
                                   :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                                   class="block w-full border-0 bg-transparent text-gray-900 dark:text-white py-2.5 px-3 text-left focus:ring-0 focus:outline-none text-sm font-semibold" dir="ltr">
                            <span class="bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 px-3 py-2.5 text-xs font-bold border-r border-gray-300 dark:border-gray-700 flex items-center">دقیقه</span>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">مقدار 0 یعنی آنی. مقادیر مثبت پس از اتمام رویداد و مقادیر منفی قبل از آن ارسال می‌شوند.</p>
                    </div>
                </div>

                {{-- Pattern vs Text --}}
                <div class="bg-gray-50/50 dark:bg-gray-900/10 border border-gray-200 dark:border-gray-800 rounded-xl p-4 space-y-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">کد الگوی سامانه پیامک (Pattern Code)</label>
                        <input type="text" name="config[pattern_key]" value="{{ $cfg['pattern_key'] ?? '' }}" placeholder="مثال: 34567 (در صورت استفاده از پیامک معمولی، خالی بگذارید)"
                               :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                               class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2.5 px-3 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Pattern Parameters --}}
                    <div class="space-y-2.5">
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">پارامترهای الگو (به ترتیب {0}, {1}, ...)</label>
                        <div class="space-y-2.5" id="pattern-params-list-{{ $alpineId }}">
                            @php
                                $currentParams = $cfg['params'] ?? [];
                                if (empty($currentParams)) $currentParams = [''];
                            @endphp

                            @foreach($currentParams as $idx => $val)
                                <div class="flex gap-2 items-center param-row">
                                    <span class="text-xs text-gray-400 w-8 text-center font-bold param-index">{{ '{' . $idx . '}' }}</span>
                                    <select name="config[params][]" class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2 px-3 text-sm">
                                        <option value="">-- انتخاب مقدار --</option>
                                        @foreach($groupedTokens as $group => $tokens)
                                            <optgroup label="{{ $group === 'appointment' ? 'مشخصات نوبت' : ($group === 'statement' ? 'صورت وضعیت مالی' : $group) }}">
                                                @foreach($tokens as $k => $l)
                                                    <option value="{{ $k }}" @selected($val === $k)>{{ $l }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    <button type="button" onclick="removeParamRow(this, '{{ $alpineId }}')" class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/20" title="حذف پارامتر">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" onclick="addParamRow('{{ $alpineId }}')" class="inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-700 font-bold mt-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            افزودن پارامتر بعدی
                        </button>
                    </div>
                </div>

                {{-- Free Text Message with Floating Token Library --}}
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">متن پیام (در صورت عدم استفاده از الگو)</label>
                    
                    {{-- Upgraded Floating Token Library Drawer --}}
                    <div class="bg-gray-50/80 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700/60 rounded-xl p-3.5">
                        <span class="text-[11px] font-bold text-indigo-600/70 dark:text-indigo-400/70 tracking-wide block mb-2">کتابخانه توکن‌های پویا (جهت درج کلیک کنید)</span>
                        <div class="flex flex-wrap gap-2 max-h-36 overflow-y-auto p-0.5">
                            @foreach($groupedTokens as $group => $tokens)
                                <div class="w-full text-[10px] font-extrabold text-gray-400 dark:text-gray-500 border-b border-gray-200/50 dark:border-gray-700 pb-0.5 mb-1.5 mt-2.5 first:mt-0">
                                    {{ $group === 'appointment' ? 'اطلاعات نوبت‌دهی بیمار' : ($group === 'statement' ? 'اطلاعات صورت وضعیت مالی' : ($group === 'treatment_plan' ? 'اطلاعات طرح درمان' : $group)) }}
                                </div>
                                @foreach($tokens as $k => $l)
                                    <button type="button" @click="insertToken('{{ $k }}')" 
                                            class="text-[10px] font-bold bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500 dark:hover:text-white px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 transition-all shadow-sm">
                                        {{ $l }}
                                    </button>
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                    <textarea name="config[message]" rows="3.5" x-ref="smsTextarea"
                              :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                              class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-3 px-4 text-sm font-medium focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                              placeholder="متن پیامک خود را در اینجا بنویسید...">{{ $cfg['message'] ?? '' }}</textarea>
                </div>
            </div>

            {{-- Task/FollowUp Fields --}}
            <div class="space-y-5" x-show="actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}'">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">عنوان وظیفه</label>
                        <input type="text" name="config[title]" value="{{ $cfg['title'] ?? '' }}"
                               :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                               class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2.5 px-3 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">منتسب به</label>
                        <select name="config[assignee_target]" x-model="assigneeTarget"
                                :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                                class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2.5 px-3 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="CURRENT_USER">کاربر فعلی سیستم</option>
                            <option value="APPOINTMENT_PROVIDER">پزشک نوبت مربوطه</option>
                            <option value="SPECIFIC_USER">کاربر خاص مشخص شده</option>
                            <optgroup label="طرح درمان">
                                <option value="TREATMENT_PLAN_CREATOR">ایجادکننده طرح درمان</option>
                                <option value="TREATMENT_PLAN_CLIENT_ASSIGNEE">بیمار طرح درمان</option>
                                @if(isset($cureRoles))
                                    @foreach($cureRoles as $role)
                                        <option value="TREATMENT_PLAN_ROLE_{{ $role->id }}">نقش «{{ $role->name }}» در طرح درمان</option>
                                    @endforeach
                                @endif
                            </optgroup>
                            <optgroup label="پرونده کلاینت">
                                <option value="CLIENT_CREATOR">ایجادکننده پرونده کلاینت</option>
                                <option value="CLIENT_ASSIGNED_USER">کاربر منتسب به پرونده کلاینت</option>
                            </optgroup>
                            <optgroup label="تماس کلاینت">
                                <option value="CALL_CREATOR">ثبت‌کننده تماس</option>
                            </optgroup>
                            <optgroup label="وظیفه و پیگیری">
                                <option value="TASK_CREATOR">ایجادکننده وظیفه/پیگیری</option>
                                <option value="TASK_ASSIGNEE">ارجاع‌شونده وظیفه/پیگیری</option>
                            </optgroup>
                        </select>
                        <div x-show="assigneeTarget === 'SPECIFIC_USER'" class="mt-2">
                            <select name="config[assignee_id]"
                                    :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                                    class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2.5 px-3 text-sm">
                                <option value="">انتخاب کاربر...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(($cfg['assignee_id'] ?? '') == $user->id)>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">تاخیر ایجاد (روز)</label>
                        <div class="flex rounded-xl border border-gray-300 dark:border-gray-700 overflow-hidden shadow-sm bg-white dark:bg-gray-900">
                            <input type="number" name="config[offset_days]" value="{{ $cfg['offset_days'] ?? '0' }}"
                                   :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                                   class="block w-full border-0 bg-transparent text-gray-900 dark:text-white py-2.5 px-3 text-left focus:ring-0 focus:outline-none text-sm font-semibold" dir="ltr">
                            <span class="bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 px-3 py-2.5 text-xs font-bold border-r border-gray-300 dark:border-gray-700 flex items-center">روز</span>
                        </div>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 leading-normal">
                            در صورت وجود برنامه زمانی برای مسئول انتخابی، محاسبه سررسید بر اساس روزهای کاری وی انجام می‌شود؛ در غیر این صورت روزهای عادی ملاک خواهد بود.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">اولویت انجام</label>
                        <select name="config[priority]"
                                :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                                class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2.5 px-3 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="LOW" @selected(($cfg['priority'] ?? '') === 'LOW')>کم</option>
                            <option value="MEDIUM" @selected(($cfg['priority'] ?? 'MEDIUM') === 'MEDIUM')>معمولی</option>
                            <option value="HIGH" @selected(($cfg['priority'] ?? '') === 'HIGH')>زیاد</option>
                            <option value="CRITICAL" @selected(($cfg['priority'] ?? '') === 'CRITICAL')>بحرانی</option>
                        </select>
                    </div>
                     <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">وضعیت اولیه</label>
                        <select name="config[status]"
                                :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                                class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2.5 px-3 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="TODO" @selected(($cfg['status'] ?? 'TODO') === 'TODO')>در صف انجام</option>
                            <option value="IN_PROGRESS" @selected(($cfg['status'] ?? '') === 'IN_PROGRESS')>در حال انجام</option>
                            <option value="DONE" @selected(($cfg['status'] ?? '') === 'DONE')>انجام شده</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">توضیحات تکمیلی</label>
                    <textarea name="config[description]" rows="3"
                              :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                              class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-3 px-4 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="جزئیات وظیفه..."></textarea>
                </div>
            </div>

            {{-- System Notification Fields --}}
            <div class="space-y-5" x-show="actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_NOTIFICATION }}'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">گیرنده اعلان (کاربر سیستم)</label>
                        <select name="config[notification_target]" x-model="notificationTarget"
                                :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_NOTIFICATION }}'"
                                class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2.5 px-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm">
                            <option value="CURRENT_USER">کاربر فعلی سیستم</option>
                            <option value="APPOINTMENT_PROVIDER">پزشک نوبت مربوطه</option>
                            <option value="SPECIFIC_USER">کاربر خاص مشخص شده</option>
                            <optgroup label="طرح درمان">
                                <option value="TREATMENT_PLAN_CREATOR">ایجادکننده طرح درمان</option>
                                <option value="TREATMENT_PLAN_CLIENT_ASSIGNEE">بیمار طرح درمان</option>
                                @if(isset($cureRoles))
                                    @foreach($cureRoles as $role)
                                        <option value="TREATMENT_PLAN_ROLE_{{ $role->id }}">نقش «{{ $role->name }}» در طرح درمان</option>
                                    @endforeach
                                @endif
                            </optgroup>
                            <optgroup label="پرونده کلاینت">
                                <option value="CLIENT_CREATOR">ایجادکننده پرونده کلاینت</option>
                                <option value="CLIENT_ASSIGNED_USER">کاربر منتسب به پرونده کلاینت</option>
                            </optgroup>
                            <optgroup label="تماس کلاینت">
                                <option value="CALL_CREATOR">ثبت‌کننده تماس</option>
                            </optgroup>
                            <optgroup label="وظیفه و پیگیری">
                                <option value="TASK_CREATOR">ایجادکننده وظیفه/پیگیری</option>
                                <option value="TASK_ASSIGNEE">ارجاع‌شونده وظیفه/پیگیری</option>
                            </optgroup>
                        </select>
                        <div x-show="notificationTarget === 'SPECIFIC_USER'" class="mt-2">
                            <select name="config[notification_target_user_id]"
                                    :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_NOTIFICATION }}'"
                                    class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2.5 px-3 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">انتخاب کاربر...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(($cfg['notification_target_user_id'] ?? '') == $user->id)>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400">پیام اعلان سیستم</label>
                    
                    {{-- Floating Token Library for Notifications too --}}
                    <div class="bg-gray-50/80 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700/60 rounded-xl p-3.5">
                        <span class="text-[11px] font-bold text-indigo-600/70 dark:text-indigo-400/70 tracking-wide block mb-2">کتابخانه توکن‌های پویا (جهت درج کلیک کنید)</span>
                        <div class="flex flex-wrap gap-2 max-h-36 overflow-y-auto p-0.5">
                            @foreach($groupedTokens as $group => $tokens)
                                <div class="w-full text-[10px] font-extrabold text-gray-400 dark:text-gray-500 border-b border-gray-200/50 dark:border-gray-700 pb-0.5 mb-1.5 mt-2.5 first:mt-0">
                                    {{ $group === 'appointment' ? 'اطلاعات نوبت‌دهی بیمار' : ($group === 'statement' ? 'اطلاعات صورت وضعیت مالی' : ($group === 'treatment_plan' ? 'اطلاعات طرح درمان' : $group)) }}
                                </div>
                                @foreach($tokens as $k => $l)
                                    <button type="button" @click="insertToken('{{ $k }}')" 
                                            class="text-[10px] font-bold bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500 dark:hover:text-white px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 transition-all shadow-sm">
                                        {{ $l }}
                                    </button>
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                    <textarea name="config[message]" rows="3.5" x-ref="notificationTextarea"
                              :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_NOTIFICATION }}'"
                              class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-3 px-4 text-sm font-medium focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                              placeholder="پیام نوتیفیکیشن را در اینجا بنویسید...">{{ $cfg['message'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit"
                        class="inline-flex items-center px-5 py-2 text-sm font-bold text-white bg-emerald-600 border border-transparent rounded-xl shadow-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all">
                    {{ $isEdit ? 'ذخیره تغییرات' : 'افزودن عملیات' }}
                </button>
                <button type="button" @click="isOpen = false"
                        class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
                    انصراف
                </button>
                @if($isEdit)
                    <button type="submit" form="delete-action-{{ $actionInstance->id }}"
                            class="mr-auto text-sm font-bold text-red-650 hover:text-red-750"
                            onclick="return confirm('آیا از حذف این عملیات اطمینان دارید؟');">
                        حذف عملیات
                    </button>
                @endif
            </div>
        </form>

        @if($isEdit)
            <form id="delete-action-{{ $actionInstance->id }}" method="post" action="{{ route('user.workflows.actions.destroy', [$workflow, $stage, $actionInstance]) }}" class="hidden">
                @csrf
                @method('delete')
            </form>
        @endif
    </div>
</div>

<script>
    if (typeof window.addParamRow === 'undefined') {
        window.addParamRow = function(containerId) {
            const container = document.getElementById('pattern-params-list-' + containerId);
            if (!container) return;

            const count = container.children.length;
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-center param-row';
            div.innerHTML = `
                <span class="text-xs text-gray-400 w-8 text-center font-bold param-index">{${count}}</span>
                <select name="config[params][]" class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white py-2 px-3 text-sm">
                    <option value="">-- انتخاب مقدار --</option>
                    @foreach($groupedTokens as $group => $tokens)
                        <optgroup label="{{ $group === 'appointment' ? 'مشخصات نوبت' : ($group === 'statement' ? 'صورت وضعیت مالی' : $group) }}">
                            @foreach($tokens as $k => $l)
                                <option value="{{ $k }}">{{ $l }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <button type="button" onclick="removeParamRow(this, '${containerId}')" class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/20" title="حذف پارامتر">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </button>
            `;
            container.appendChild(div);
        }
    }

    if (typeof window.removeParamRow === 'undefined') {
        window.removeParamRow = function(btn, containerId) {
            const row = btn.closest('.param-row');
            const container = document.getElementById('pattern-params-list-' + containerId);
            if (row && container) {
                row.remove();
                // Re-index
                const rows = container.querySelectorAll('.param-row');
                rows.forEach((r, index) => {
                    const idxSpan = r.querySelector('.param-index');
                    if (idxSpan) idxSpan.textContent = `{${index}}`;
                });
            }
        }
    }
</script>
