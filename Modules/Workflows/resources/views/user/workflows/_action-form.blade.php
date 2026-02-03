@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $actionInstance = $action ?? null;
    $cfg = $isEdit ? ($actionInstance->config ?? []) : [];
    $formAction = $isEdit
        ? route('user.workflows.actions.update', [$workflow, $stage, $actionInstance])
        : route('user.workflows.actions.store', [$workflow, $stage]);

    $alpineId = $isEdit ? 'action_edit_' . $actionInstance->id : 'action_create_' . $stage->id;

    $types = [
        \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS => 'ارسال پیامک',
        \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK => 'ایجاد وظیفه',
        \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP => 'ایجاد پیگیری',
        \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_NOTIFICATION => 'نوتیفیکیشن سیستمی',
    ];

    // Extended Token Options
    $tokenOptions = [
        'client_name' => 'نام مشتری',
        'client_phone' => 'شماره مشتری',
        'service_name' => 'نام سرویس',
        'provider_name' => 'نام ارائه‌دهنده',
        'appointment_date_jalali' => 'تاریخ نوبت (شمسی)',
        'appointment_time_jalali' => 'ساعت نوبت',
        'appointment_datetime_jalali' => 'تاریخ و ساعت کامل',
        'payment_link' => 'لینک پرداخت (اگر باشد)',
    ];
@endphp

<div x-data="{
         actionType: '{{ $isEdit ? $actionInstance->action_type : \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}',
         assigneeTarget: '{{ $cfg['assignee_target'] ?? 'CURRENT_USER' }}',
         smsTarget: '{{ $cfg['target'] ?? 'APPOINTMENT_CLIENT' }}',
         isOpen: false,

         insertToken(token) {
             const textarea = this.$refs.messageTextarea;
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
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 flex items-center justify-between gap-4 transition hover:border-indigo-300 dark:hover:border-indigo-700">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    @if($actionInstance->action_type === \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                    @elseif(in_array($actionInstance->action_type, [\Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK, \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP]))
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    @endif
                </div>

                <div class="min-w-0">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ $types[$actionInstance->action_type] ?? $actionInstance->action_type }}
                        @if(!empty($cfg['title']))
                            <span class="text-gray-500 dark:text-gray-400 font-normal">- {{ $cfg['title'] }}</span>
                        @endif
                    </h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        @if($actionInstance->action_type === \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS)
                            {{ Str::limit($cfg['message'] ?? ($cfg['pattern_key'] ? "الگو: {$cfg['pattern_key']}" : ''), 50) }}
                        @else
                            {{ Str::limit($cfg['description'] ?? '', 50) }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-xs text-gray-400 font-mono bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">#{{ $actionInstance->sort_order }}</span>
                <button type="button" @click="isOpen = !isOpen" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors p-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- 2. CREATE BUTTON --}}
    @if(!$isEdit)
        <button type="button" @click="isOpen = !isOpen" x-show="!isOpen"
                class="inline-flex items-center px-3 py-2 text-xs font-medium text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-md hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-800 dark:hover:bg-indigo-900/50 transition-colors w-full justify-center border-dashed">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            افزودن عملیات جدید
        </button>
    @endif

    {{-- 3. FORM --}}
    <div x-show="isOpen" x-transition
         class="{{ $isEdit ? 'mt-2' : '' }} bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-4 relative">

        <button type="button" @click="isOpen = false" class="absolute top-2 left-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>

        <form method="post" action="{{ $formAction }}" class="space-y-4 pt-2">
            @csrf
            @if($isEdit)
                @method('patch')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">نوع اکشن</label>
                    <select name="action_type" x-model="actionType"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        @foreach($types as $type => $label)
                            <option value="{{ $type }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">ترتیب</label>
                    <input type="number" name="sort_order" value="{{ $isEdit ? $actionInstance->sort_order : 0 }}" min="0"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700 border-dashed">

            {{-- SMS Fields --}}
            <div class="space-y-4" x-show="actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Target Selection --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">هدف ارسال (گیرنده)</label>
                        <select name="config[target]" x-model="smsTarget"
                                :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            <option value="APPOINTMENT_CLIENT">مشتری نوبت</option>
                            <option value="APPOINTMENT_PROVIDER">ارائه‌دهنده نوبت</option>
                            <option value="SPECIFIC_USER">کاربر خاص سیستم</option>
                            <option value="CUSTOM_PHONE">شماره دلخواه</option>
                        </select>

                        {{-- Dynamic Target Inputs --}}
                        <div class="mt-2 space-y-2">
                            <div x-show="smsTarget === 'CUSTOM_PHONE'">
                                <input type="text" name="config[phone]" value="{{ $cfg['phone'] ?? '' }}" placeholder="شماره موبایل (مثلاً 0912...)"
                                       :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            </div>
                            <div x-show="smsTarget === 'SPECIFIC_USER'">
                                <select name="config[target_user_id]"
                                        :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                                    <option value="">انتخاب کاربر...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected(($cfg['target_user_id'] ?? '') == $user->id)>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Offset --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">زمان ارسال (تاخیر)</label>
                        <div class="relative rounded-md shadow-sm">
                            <input type="number" name="config[offset_minutes]" value="{{ $cfg['offset_minutes'] ?? '0' }}"
                                   :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                                   class="block w-full rounded-md border-gray-300 pr-2 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white text-left" dir="ltr">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">دقیقه</span>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">0 = آنی. مثبت = بعد از نوبت. منفی = قبل از نوبت (فقط در تریگرهای زمان‌دار).</p>
                    </div>
                </div>

                {{-- Pattern vs Text --}}
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-md p-3">
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">کد الگو (Pattern Code)</label>
                        <input type="text" name="config[pattern_key]" value="{{ $cfg['pattern_key'] ?? '' }}" placeholder="مثال: 34567 (خالی بگذارید اگر متن معمولی است)"
                               :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                    </div>

                    {{-- Pattern Parameters --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">پارامترهای الگو (به ترتیب {0}, {1}, ...)</label>
                        <div class="space-y-2" id="pattern-params-list-{{ $alpineId }}">
                            @php
                                $currentParams = $cfg['params'] ?? [];
                                // Ensure at least one input if empty, or show existing
                                if (empty($currentParams)) $currentParams = [''];
                            @endphp

                            @foreach($currentParams as $idx => $val)
                                <div class="flex gap-2 items-center param-row">
                                    <span class="text-xs text-gray-400 w-6 text-center param-index">{{ '{' . $idx . '}' }}</span>
                                    <select name="config[params][]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                        <option value="">-- انتخاب مقدار --</option>
                                        @foreach($tokenOptions as $k => $l)
                                            <option value="{{ $k }}" @selected($val === $k)>{{ $l }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" onclick="removeParamRow(this, '{{ $alpineId }}')" class="text-red-500 hover:text-red-700 p-1" title="حذف پارامتر">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" onclick="addParamRow('{{ $alpineId }}')" class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            افزودن پارامتر بعدی
                        </button>
                    </div>
                </div>

                {{-- Free Text Message --}}
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">متن پیام (اگر الگو ندارید)</label>
                        <div class="flex gap-1">
                            @foreach($tokenOptions as $k => $l)
                                <button type="button" @click="insertToken('{{ $k }}')" class="text-[10px] bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-300 transition-colors" title="{{ $l }}">
                                    {{ $l }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <textarea name="config[message]" rows="3" x-ref="messageTextarea"
                              :disabled="actionType !== '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_SEND_SMS }}'"
                              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white font-mono text-sm"
                              placeholder="متن پیامک...">{{ $cfg['message'] ?? '' }}</textarea>
                </div>
            </div>

            {{-- Task/FollowUp Fields --}}
            <div class="space-y-4" x-show="actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}'">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">عنوان</label>
                        <input type="text" name="config[title]" value="{{ $cfg['title'] ?? '' }}"
                               :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">منتسب به</label>
                        <select name="config[assignee_target]" x-model="assigneeTarget"
                                :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            <option value="CURRENT_USER">کاربر فعلی</option>
                            <option value="APPOINTMENT_PROVIDER">ارائه‌دهنده نوبت</option>
                            <option value="SPECIFIC_USER">کاربر خاص</option>
                        </select>
                        <div x-show="assigneeTarget === 'SPECIFIC_USER'" class="mt-2">
                            <select name="config[assignee_id]"
                                    :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                                <option value="">انتخاب کاربر...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(($cfg['assignee_id'] ?? '') == $user->id)>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">تاخیر ایجاد (روز)</label>
                        <input type="number" name="config[offset_days]" value="{{ $cfg['offset_days'] ?? '0' }}"
                               :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">نسبت به زمان شروع نوبت.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">اولویت</label>
                        <select name="config[priority]"
                                :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            <option value="LOW" @selected(($cfg['priority'] ?? '') === 'LOW')>کم</option>
                            <option value="MEDIUM" @selected(($cfg['priority'] ?? 'MEDIUM') === 'MEDIUM')>معمولی</option>
                            <option value="HIGH" @selected(($cfg['priority'] ?? '') === 'HIGH')>زیاد</option>
                            <option value="CRITICAL" @selected(($cfg['priority'] ?? '') === 'CRITICAL')>بحرانی</option>
                        </select>
                    </div>
                     <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">وضعیت</label>
                        <select name="config[status]"
                                :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            <option value="TODO" @selected(($cfg['status'] ?? 'TODO') === 'TODO')>در صف انجام</option>
                            <option value="IN_PROGRESS" @selected(($cfg['status'] ?? '') === 'IN_PROGRESS')>در حال انجام</option>
                            <option value="DONE" @selected(($cfg['status'] ?? '') === 'DONE')>انجام شده</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">توضیحات</label>
                    <textarea name="config[description]" rows="3"
                              :disabled="! (actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_TASK }}' || actionType === '{{ \Modules\Workflows\Entities\WorkflowAction::TYPE_CREATE_FOLLOWUP }}')"
                              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">{{ $cfg['description'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit"
                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-md shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:focus:ring-offset-gray-800">
                    {{ $isEdit ? 'ذخیره تغییرات' : 'افزودن اکشن' }}
                </button>
                <button type="button" @click="isOpen = false"
                        class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                    انصراف
                </button>
                @if($isEdit)
                    <button type="submit" form="delete-action-{{ $actionInstance->id }}"
                            class="ml-auto text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                            onclick="return confirm('آیا از حذف این اکشن اطمینان دارید؟');">
                        حذف اکشن
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
                <span class="text-xs text-gray-400 w-6 text-center param-index">{${count}}</span>
                <select name="config[params][]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                    <option value="">-- انتخاب مقدار --</option>
                    @foreach($tokenOptions as $k => $l)
                        <option value="{{ $k }}">{{ $l }}</option>
                    @endforeach
                </select>
                <button type="button" onclick="removeParamRow(this, '${containerId}')" class="text-red-500 hover:text-red-700 p-1" title="حذف پارامتر">
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
